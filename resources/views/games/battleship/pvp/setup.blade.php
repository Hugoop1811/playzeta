{{-- resources/views/games/battleship/pvp/setup.blade.php --}}
@extends('layout')

@section('content')
<div class="grid justify-center mx-auto p-6 min-h-screen">
  <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Coloca tus barcos (Multijugador)</h1>
    <p class="mb-6 text-gray-300">
      Arrastra tus barcos al tablero de 10×10. Cuando ambos jugadores hayan colocado los suyos, la partida comenzará automáticamente.
    </p>

    <div class="flex space-x-8">
      {{-- Tablero del jugador --}}
      <div>
        <button id="rotate-btn"
                class="mb-4 px-3 py-1 bg-blue-600 text-white rounded">
          Orientación: Horizontal
        </button>
        <div id="player-board"
             class="grid grid-rows-10 grid-cols-10 gap-[0.05rem] p-2">
          @for($y=0; $y<10; $y++)
            @for($x=0; $x<10; $x++)
              <div
                class="battleship-cell w-10 h-10 bg-blue-700 border border-blue-700"
                data-x="{{ $x }}" data-y="{{ $y }}"
              ></div>
            @endfor
          @endfor
        </div>
        <div id="status" class="mt-3 text-lg text-yellow-400"></div>
      </div>

      {{-- Paleta de barcos --}}
      <div class="space-y-4">
        @php
          $ships = [
            ['id'=>'ship-5','size'=>5,'label'=>'Portaaviones','color'=>'bg-red-500'],
            ['id'=>'ship-4','size'=>4,'label'=>'Acorazado','color'=>'bg-green-500'],
            ['id'=>'ship-3','size'=>3,'label'=>'Submarino','color'=>'bg-yellow-500'],
            ['id'=>'ship-3b','size'=>3,'label'=>'Crucero','color'=>'bg-purple-500'],
            ['id'=>'ship-2','size'=>2,'label'=>'Destructor','color'=>'bg-indigo-500'],
          ];
        @endphp

        @foreach($ships as $ship)
          <div draggable="true"
               data-id="{{ $ship['id'] }}"
               data-size="{{ $ship['size'] }}"
               data-color="{{ $ship['color'] }}"
               class="ship {{ $ship['color'] }} text-white px-4 py-2 rounded cursor-grab hover:opacity-90">
            {{ $ship['label'] }} ({{ $ship['size'] }})
          </div>
        @endforeach

        <button id="start-game"
                class="mt-6 w-full px-4 py-2 bg-green-600 text-white font-semibold rounded disabled:opacity-50"
                disabled>
          ¡A jugar!
        </button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const gameId   = {{ $battleship_game->id }};
  const setupUrl = "{{ route('battleship.pvp.setup', $battleship_game) }}";
  const channel  = Echo.private(`battleship.pvp.${gameId}`);
  const statusEl = document.getElementById('status');

  const boardEl   = document.getElementById('player-board');
  const rotateBtn = document.getElementById('rotate-btn');
  const shipEls   = Array.from(document.querySelectorAll('.ship'));
  const startBtn  = document.getElementById('start-game');

  let orientation   = 'horizontal';
  let currentShip   = null;
  let previewCells  = [];
  const placedShips = [];

  // Escucha cuando el rival coloca sus barcos
  channel.listen('ShipsPlaced', e => {
    // Si yo ya coloqué y viene del otro jugador, arrancamos
    if (placedShips.length === shipEls.length && e.playerId !== {{ auth()->id() }}) {
      window.location.href = "{{ route('battleship.pvp.play', $battleship_game) }}";
    }
  });

  // Alternar orientación de colocación
  rotateBtn.addEventListener('click', () => {
    orientation = orientation === 'horizontal' ? 'vertical' : 'horizontal';
    rotateBtn.textContent = 'Orientación: ' + (orientation === 'horizontal' ? 'Horizontal' : 'Vertical');
  });

  // Preparar dragstart para cada barco
  shipEls.forEach(el => {
    el.addEventListener('dragstart', () => {
      currentShip = {
        id: el.dataset.id,
        size: +el.dataset.size,
        color: el.dataset.color,
        el
      };
    });
  });

  // Función para limpiar preview
  function clearPreview() {
    previewCells.forEach(c => {
      c.classList.remove(currentShip?.color);
      c.classList.add('bg-blue-700');
    });
    previewCells = [];
  }

  // Dragover, dragleave y drop en celdas
  boardEl.querySelectorAll('.battleship-cell').forEach(cell => {
    cell.addEventListener('dragover', e => {
      e.preventDefault();
      if (!currentShip) return;
      clearPreview();

      const x = +cell.dataset.x, y = +cell.dataset.y;
      const cells = [];
      for (let i = 0; i < currentShip.size; i++) {
        const xi = orientation === 'horizontal' ? x + i : x;
        const yi = orientation === 'vertical'   ? y + i : y;
        if (xi > 9 || yi > 9) { clearPreview(); return; }
        const c = boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`);
        if (c.dataset.occupiedBy) { clearPreview(); return; }
        cells.push(c);
      }
      cells.forEach(c => {
        c.classList.remove('bg-blue-700');
        c.classList.add(currentShip.color);
      });
      previewCells = cells;
    });

    cell.addEventListener('dragleave', () => {
      if (!currentShip) return;
      clearPreview();
    });

    cell.addEventListener('drop', () => {
      if (!currentShip || previewCells.length !== currentShip.size) return;

      // Fijar barco definitivamente
      previewCells.forEach(c => {
        c.dataset.occupiedBy = currentShip.id;
      });

      placedShips.push({
        size: currentShip.size,
        cells: previewCells.map(c => [+c.dataset.x, +c.dataset.y])
      });

      currentShip.el.draggable = false;
      currentShip.el.classList.add('opacity-50','cursor-not-allowed');

      currentShip = null;
      previewCells = [];

      if (placedShips.length === shipEls.length) {
        startBtn.disabled = false;
      }
    });
  });

  // Al hacer clic en “¡A jugar!”, enviamos al servidor
  startBtn.addEventListener('click', async () => {
    statusEl.textContent = 'Esperando rival…';
    try {
      const res = await fetch(setupUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ ships: placedShips })
      });
      const json = await res.json();
      if (!json.ok) {
        statusEl.textContent = 'Error guardando posiciones';
        return;
      }
      // Si ya ambos colocaron, arrancamos
      if (json.start) {
        window.location.href = "{{ route('battleship.pvp.play', $battleship_game) }}";
      }
    } catch (e) {
      console.error(e);
      statusEl.textContent = 'Error de red';
    }
  });
});
</script>
@endpush