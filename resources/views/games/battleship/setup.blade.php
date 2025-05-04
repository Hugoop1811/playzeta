{{-- resources/views/games/battleship/setup.blade.php --}}
@extends('layout')

@section('content')
<div class="battleship-container p-6">
  <h1 class="text-3xl font-bold mb-4">Coloca tus barcos</h1>
  <p class="mb-6">
    Arrastra y sitúa tus barcos en el tablero de 10×10. Cuando hayas colocado todos, haz clic en “¡A jugar!”.
  </p>

  <div class="flex items-start">
    {{-- Orientación --}}
    <div class="mr-8">
      <button id="rotate-btn" class="mb-4 px-3 py-1 bg-blue-600 text-white rounded">
        Orientación: Horizontal
      </button>
      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-1">
        @for ($y = 0; $y < 10; $y++)
          @for ($x = 0; $x < 10; $x++)
            <div
              class="battleship-cell w-8 h-8 border border-gray-600 bg-gray-800"
              data-x="{{ $x }}" data-y="{{ $y }}"
            ></div>
          @endfor
        @endfor
      </div>
    </div>

    {{-- Paleta de barcos --}}
    <div class="flex flex-col">
      @php
        $ships = [
          ['id'=>'ship-0','size'=>5,'label'=>'Portaaviones','color'=>'bg-red-600','preview'=>'bg-red-400'],
          ['id'=>'ship-1','size'=>4,'label'=>'Acorazado','color'=>'bg-blue-600','preview'=>'bg-blue-400'],
          ['id'=>'ship-2','size'=>3,'label'=>'Submarino','color'=>'bg-green-600','preview'=>'bg-green-400'],
          ['id'=>'ship-3','size'=>3,'label'=>'Crucero','color'=>'bg-yellow-600','preview'=>'bg-yellow-400'],
          ['id'=>'ship-4','size'=>2,'label'=>'Destructor','color'=>'bg-purple-600','preview'=>'bg-purple-400'],
        ];
      @endphp

      @foreach ($ships as $ship)
        <div
          draggable="true"
          data-id="{{ $ship['id'] }}"
          data-size="{{ $ship['size'] }}"
          data-color="{{ $ship['color'] }}"
          data-preview="{{ $ship['preview'] }}"
          class="ship mb-3 p-2 text-white text-center rounded cursor-grab hover:opacity-90 {{ $ship['color'] }}"
        >
          {{ $ship['label'] }} ({{ $ship['size'] }})
        </div>
      @endforeach

      <button
        id="start-game"
        class="mt-6 px-4 py-2 bg-green-600 text-white font-medium rounded disabled:bg-gray-500"
        disabled
      >
        ¡A jugar!
      </button>
    </div>
  </div>

  <div id="status" class="mt-4 text-lg font-semibold"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const rotateBtn = document.getElementById('rotate-btn');
  const boardEl   = document.getElementById('player-board');
  const shipEls   = Array.from(document.querySelectorAll('.ship'));
  const startBtn  = document.getElementById('start-game');
  const statusEl  = document.getElementById('status');
  const setupUrl  = "{{ route('battleship.setup', ['battleship_game' => $battleship_game->id]) }}";

  let orientation   = 'horizontal';
  let currentShip   = null;
  let previewCells  = [];
  const placedShips = [];

  // Alternar orientación
  rotateBtn.addEventListener('click', () => {
    orientation = orientation === 'horizontal' ? 'vertical' : 'horizontal';
    rotateBtn.textContent =
      'Orientación: ' + (orientation === 'horizontal' ? 'Horizontal' : 'Vertical');
  });

  // Iniciar drag de la paleta
  shipEls.forEach(el => {
    el.addEventListener('dragstart', () => {
      currentShip = {
        id: el.dataset.id,
        size: +el.dataset.size,
        color: el.dataset.color,
        preview: el.dataset.preview,
        el
      };
    });
  });

  // Limpiar preview
  function clearPreview() {
    if (!currentShip) {
      previewCells = [];
      return;
    }
    previewCells.forEach(c => {
      c.classList.remove(currentShip.preview);
      c.classList.add('bg-gray-800');
    });
    previewCells = [];
  }

  // Dragover / dragleave / drop
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
        const c = boardEl.querySelector(
          `.battleship-cell[data-x="${xi}"][data-y="${yi}"]`
        );
        if (c.dataset.occupiedBy) { clearPreview(); return; }
        cells.push(c);
      }
      cells.forEach(c => {
        c.classList.remove('bg-gray-800');
        c.classList.add(currentShip.preview);
      });
      previewCells = cells;
    });

    cell.addEventListener('dragleave', () => {
      if (!currentShip) return;
      clearPreview();
    });

    cell.addEventListener('drop', () => {
      if (!currentShip || previewCells.length !== currentShip.size) return;

      // Colocar barco
      previewCells.forEach(c => {
        c.classList.remove(currentShip.preview);
        c.classList.add(currentShip.color);
        c.dataset.occupiedBy = currentShip.id;
      });

      // Registro del barco colocado
      const coords = previewCells.map(c => [+c.dataset.x, +c.dataset.y]);
      const shipRecord = {
        id:    currentShip.id,
        size:  currentShip.size,
        cells: coords,
        color: currentShip.color,
        el:    currentShip.el
      };
      placedShips.push(shipRecord);

      // Bloquear barco en paleta
      currentShip.el.draggable = false;
      currentShip.el.classList.add('opacity-50','cursor-not-allowed');

      // Permitir recolocar al hacer clic en cualquiera de sus celdas
      shipRecord.cells.forEach(([xi, yi]) => {
        const cellClick = boardEl.querySelector(
          `.battleship-cell[data-x="${xi}"][data-y="${yi}"]`
        );
        const handler = () => {
          // Quitar visual de todas las celdas del barco
          shipRecord.cells.forEach(([x0, y0]) => {
            const c0 = boardEl.querySelector(
              `.battleship-cell[data-x="${x0}"][data-y="${y0}"]`
            );
            c0.classList.remove(shipRecord.color);
            c0.classList.add('bg-gray-800');
            delete c0.dataset.occupiedBy;
            c0.removeEventListener('click', handler);
          });
          // Reactivar barco en paleta
          shipRecord.el.draggable = true;
          shipRecord.el.classList.remove('opacity-50','cursor-not-allowed');
          // Eliminar del array placedShips
          const idx = placedShips.findIndex(s => s.id === shipRecord.id);
          if (idx !== -1) placedShips.splice(idx,1);
          // Desactivar “¡A jugar!” si ya no están todos
          if (placedShips.length !== shipEls.length) {
            startBtn.setAttribute('disabled','disabled');
            statusEl.textContent = '';
          }
        };
        cellClick.addEventListener('click', handler);
      });

      // Limpiar estado de preview y currentShip
      currentShip = null;
      previewCells = [];

      // Habilitar botón si todos los barcos están colocados
      if (placedShips.length === shipEls.length) {
        startBtn.removeAttribute('disabled');
        statusEl.textContent = '¡Listo para jugar!';
      }
    });
  });

  // Enviar posiciones al servidor
  startBtn.addEventListener('click', async () => {
    statusEl.textContent = '';
    try {
      const res = await fetch(setupUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'Accept':       'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
          ships: placedShips.map(s => ({ size: s.size, cells: s.cells }))
        })
      });
      const json = await res.json();

      if (!res.ok) {
        console.error('Error 500 del servidor:', json.message);
        return statusEl.textContent = `Error interno: ${json.message}`;
      }
      if (!json.ok) {
        return alert('Error guardando posiciones');
      }
      if (json.start) {
        window.location.href =
          "{{ route('battleship.play', ['battleship_game' => $battleship_game->id]) }}";
      } else {
        statusEl.textContent = 'Esperando a que el rival coloque sus barcos…';
      }
    } catch (err) {
      console.error('Fetch error:', err);
      statusEl.textContent = 'Error de red, inténtalo de nuevo';
    }
  });
});
</script>
@endpush