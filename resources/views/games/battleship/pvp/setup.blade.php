@extends('layout')

@section('content')
  <div class="grid justify-center mx-auto p-6">
    <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Coloca tus barcos</h1>
    <p class="mb-6 text-gray-300">
      Arrastra los barcos al tablero de 10×10. Cuando termines, haz clic en “¡A jugar!”. La partida empezará cuando
      ambos estén listos.
    </p>

    <div class="flex space-x-8">
      <div>
      <button id="rotate-btn" class="mb-4 px-3 py-1 bg-blue-600 text-white rounded">
        Orientación: Horizontal
      </button>

      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem] p-2">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
      <div class="battleship-cell w-10 h-10 bg-blue-700 border border-blue-700 touch-none select-none"
      data-x="{{ $x }}" data-y="{{ $y }}">
      </div>
      @endfor
      @endfor
      </div>

      <div id="status" class="mt-3 text-lg text-yellow-400"></div>
      </div>

      <div class="space-y-4">
      @php
      $ships = [
      ['id' => 'ship-5', 'size' => 5, 'label' => 'Portaaviones', 'color' => 'bg-red-500'],
      ['id' => 'ship-4', 'size' => 4, 'label' => 'Acorazado', 'color' => 'bg-green-500'],
      ['id' => 'ship-3', 'size' => 3, 'label' => 'Submarino', 'color' => 'bg-yellow-500'],
      ['id' => 'ship-3b', 'size' => 3, 'label' => 'Crucero', 'color' => 'bg-purple-500'],
      ['id' => 'ship-2', 'size' => 2, 'label' => 'Destructor', 'color' => 'bg-indigo-500'],
      ];
    @endphp

      @foreach($ships as $ship)
      <div draggable="true" data-id="{{ $ship['id'] }}" data-size="{{ $ship['size'] }}"
      data-color="{{ $ship['color'] }}"
      class="ship {{ $ship['color'] }} text-white px-4 py-2 rounded cursor-grab hover:opacity-90 touch-none select-none">
      {{ $ship['label'] }} ({{ $ship['size'] }})
      </div>
    @endforeach

      <button id="start-game"
        class="mt-6 w-full px-4 py-2 bg-green-600 text-white font-semibold rounded disabled:opacity-50" disabled>
        ¡A jugar!
      </button>
      </div>
    </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    const gameId = {{ $battleship_game->id }};
    const userId = {{ auth()->id() }};
    const setupUrl = "{{ route('battleship.pvp.setup', $battleship_game) }}";
    const playUrl = "{{ route('battleship.pvp.play', $battleship_game) }}";

    const boardEl = document.getElementById('player-board');
    const rotateBtn = document.getElementById('rotate-btn');
    const shipEls = Array.from(document.querySelectorAll('.ship'));
    const startBtn = document.getElementById('start-game');
    const statusEl = document.getElementById('status');

    let orientation = 'horizontal';
    let currentShip = null;
    let previewCells = [];
    const placedShips = [];

    rotateBtn.addEventListener('click', () => {
      orientation = orientation === 'horizontal' ? 'vertical' : 'horizontal';
      rotateBtn.textContent = 'Orientación: ' + (orientation === 'horizontal' ? 'Horizontal' : 'Vertical');
    });

    shipEls.forEach(el => {
      el.addEventListener('dragstart', () => {
      removeShipIfPlaced(el);
      setCurrentShip(el);
      });

      // Soporte táctil para móviles
      el.addEventListener('touchstart', e => {
      e.preventDefault();
      removeShipIfPlaced(el);
      setCurrentShip(el);

      const moveHandler = e2 => {
        e2.preventDefault();
        clearPreview();
        const touch = e2.touches[0];
        const target = document.elementFromPoint(touch.clientX, touch.clientY);
        if (!target || !target.classList.contains('battleship-cell')) return;
        previewPlacement(+target.dataset.x, +target.dataset.y);
      };

      const endHandler = e3 => {
        if (currentShip && previewCells.length === currentShip.size) {
        placeShip(previewCells);
        }
        document.removeEventListener('touchmove', moveHandler);
        document.removeEventListener('touchend', endHandler);
      };

      document.addEventListener('touchmove', moveHandler, { passive: false });
      document.addEventListener('touchend', endHandler);
      });
    });

    function setCurrentShip(el) {
      currentShip = {
      id: el.dataset.id,
      size: +el.dataset.size,
      color: el.dataset.color,
      el
      };
    }

    function removeShipIfPlaced(el) {
      const id = el.dataset.id;
      const existing = placedShips.find(s => s.id === id);
      if (existing) {
      existing.cells.forEach(([x, y]) => {
        const c = boardEl.querySelector(`[data-x="${x}"][data-y="${y}"]`);
        c.className = 'battleship-cell w-10 h-10 bg-blue-700 border border-blue-700';
        delete c.dataset.occupiedBy;
      });
      const idx = placedShips.findIndex(s => s.id === id);
      if (idx !== -1) placedShips.splice(idx, 1);
      el.classList.remove('bg-gray-600', 'opacity-50');
      el.classList.add(el.dataset.color);
      startBtn.disabled = true;
      statusEl.textContent = '';
      }
    }

    function clearPreview() {
      previewCells.forEach(c => {
      c.classList.remove('bg-blue-900');
      c.classList.add('bg-blue-700');
      });
      previewCells = [];
    }

    function isBlocked(x, y) {
      const cell = boardEl.querySelector(`[data-x="${x}"][data-y="${y}"]`);
      if (cell.dataset.occupiedBy) return true;
      const dirs = [[1, 0], [-1, 0], [0, 1], [0, -1]];
      for (let [dx, dy] of dirs) {
      const nx = x + dx, ny = y + dy;
      if (nx < 0 || nx > 9 || ny < 0 || ny > 9) continue;
      const ncell = boardEl.querySelector(`[data-x="${nx}"][data-y="${ny}"]`);
      if (ncell.dataset.occupiedBy) return true;
      }
      return false;
    }

    function previewPlacement(x, y) {
      const cells = [];
      for (let i = 0; i < currentShip.size; i++) {
      const xi = orientation === 'horizontal' ? x + i : x;
      const yi = orientation === 'vertical' ? y + i : y;
      if (xi > 9 || yi > 9 || isBlocked(xi, yi)) {
        clearPreview();
        return;
      }
      const cell = boardEl.querySelector(`[data-x="${xi}"][data-y="${yi}"]`);
      cells.push(cell);
      }
      cells.forEach(c => {
      c.classList.remove('bg-blue-700');
      c.classList.add('bg-blue-900');
      });
      previewCells = cells;
    }

    function placeShip(cells) {
      const shipRecord = {
      id: currentShip.id,
      size: currentShip.size,
      color: currentShip.color,
      el: currentShip.el,
      cells: cells.map(c => [+c.dataset.x, +c.dataset.y])
      };

      shipRecord.cells.forEach(([xi, yi]) => {
      const c = boardEl.querySelector(`[data-x="${xi}"][data-y="${yi}"]`);
      c.classList.remove('bg-blue-900');
      c.classList.add(shipRecord.color);
      c.dataset.occupiedBy = shipRecord.id;
      });

      placedShips.push(shipRecord);

      shipRecord.el.classList.remove(shipRecord.color);
      shipRecord.el.classList.add('bg-gray-600', 'opacity-50');

      shipRecord.cells.forEach(([xi, yi]) => {
      const c = boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`);
      c.addEventListener('click', () => {
        shipRecord.cells.forEach(([x0, y0]) => {
        const cc = boardEl.querySelector(`.battleship-cell[data-x="${x0}"][data-y="${y0}"]`);
        cc.classList.remove(shipRecord.color);
        cc.classList.add('bg-blue-700');
        delete cc.dataset.occupiedBy;
        });
        shipRecord.el.draggable = true;
        shipRecord.el.classList.remove('bg-gray-600', 'opacity-50');
        shipRecord.el.classList.add(shipRecord.color);
        const idx = placedShips.findIndex(s => s.id === shipRecord.id);
        placedShips.splice(idx, 1);
        startBtn.disabled = true;
        statusEl.textContent = '';
      });
      });

      clearPreview();
      currentShip = null;

      const uniquePlacedShips = [...new Set(placedShips.map(s => s.id))];
      if (uniquePlacedShips.length === shipEls.length) {
      startBtn.disabled = false;
      statusEl.textContent = '¡Listo para jugar!';
      }
    }

    boardEl.querySelectorAll('.battleship-cell').forEach(cell => {
      cell.addEventListener('dragover', e => {
      e.preventDefault();
      if (!currentShip) return;
      clearPreview();
      previewPlacement(+cell.dataset.x, +cell.dataset.y);
      });

      cell.addEventListener('dragleave', () => {
      if (!currentShip) return;
      clearPreview();
      });

      cell.addEventListener('drop', () => {
      if (!currentShip || previewCells.length !== currentShip.size) return;
      placeShip(previewCells);
      });
    });

    startBtn.addEventListener('click', async () => {
      statusEl.textContent = '';
      try {
      const res = await fetch(setupUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ships: placedShips.map(s => ({ size: s.size, cells: s.cells })) })
      });
      const json = await res.json();
      if (json.start) {
        window.location.href = playUrl;
      } else {
        statusEl.textContent = 'Esperando a que el rival coloque sus barcos…';
      }
      } catch (e) {
      console.error(e);
      statusEl.textContent = 'Error de red.';
      }
    });

    const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
      cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
      authEndpoint: '/broadcasting/auth',
      auth: {
      headers: {
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
      }
    });

    window.Echo.private(`battleship.pvp.${gameId}`)
      .listen('.GameReady', () => {
      window.location.href = playUrl;
      });
    });
  </script>
@endpush