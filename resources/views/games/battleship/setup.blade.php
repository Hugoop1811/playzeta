{{-- resources/views/games/battleship/setup.blade.php --}}
@extends('layout')

@section('content')
<div class="grid justify-center mx-auto p-6">
  <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Coloca tus barcos</h1>
    <p class="mb-6 text-gray-300">
      Arrastra los barcos al tablero de 10×10. Cuando termines, haz clic en “¡A jugar!”.
    </p>

    <div class="flex space-x-8">
      {{-- Tablero y botón de orientación --}}
      <div>
        <button id="rotate-btn"
                class="mb-4 px-3 py-1 bg-blue-600 text-white rounded">
          Orientación: Horizontal
        </button>

        <div id="player-board"
             class="grid grid-rows-10 grid-cols-10 gap-[0.05rem] p-2">
          @for($y = 0; $y < 10; $y++)
            @for($x = 0; $x < 10; $x++)
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
          <div
            draggable="true"
            data-id="{{ $ship['id'] }}"
            data-size="{{ $ship['size'] }}"
            data-color="{{ $ship['color'] }}"
            class="ship {{ $ship['color'] }} text-white px-4 py-2 rounded cursor-grab hover:opacity-90"
          >
            {{ $ship['label'] }} ({{ $ship['size'] }})
          </div>
        @endforeach

        <button
          id="start-game"
          class="mt-6 w-full px-4 py-2 bg-green-600 text-white font-semibold rounded disabled:opacity-50"
          disabled
        >
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
  const boardEl    = document.getElementById('player-board');
  const shipEls    = Array.from(document.querySelectorAll('.ship'));
  const rotateBtn  = document.getElementById('rotate-btn');
  const startBtn   = document.getElementById('start-game');
  const statusEl   = document.getElementById('status');
  const setupUrl   = "{{ route('battleship.setup', ['battleship_game'=>$battleship_game->id]) }}";

  // Sólo horizontal/vertical
  let orientation = 'horizontal';
  rotateBtn.addEventListener('click', () => {
    orientation = orientation === 'horizontal' ? 'vertical' : 'horizontal';
    rotateBtn.textContent = 'Orientación: ' + (orientation === 'horizontal' ? 'Horizontal' : 'Vertical');
  });

  let currentShip  = null;
  let previewCells = [];
  const placedShips = [];

  // Dragstart
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

  // Limpiar preview
  function clearPreview() {
    previewCells.forEach(c => {
      c.classList.remove('bg-blue-900');
      c.classList.add('bg-blue-700');
    });
    previewCells = [];
  }

  // Comprueba vecinos ortogonales
  function isBlocked(x, y) {
    const cell = boardEl.querySelector(`.battleship-cell[data-x="${x}"][data-y="${y}"]`);
    if (cell.dataset.occupiedBy) return true;
    const dirs = [[1,0],[-1,0],[0,1],[0,-1]];
    for (let [dx,dy] of dirs) {
      const nx=x+dx, ny=y+dy;
      if (nx<0||nx>9||ny<0||ny>9) continue;
      const ncell = boardEl.querySelector(`.battleship-cell[data-x="${nx}"][data-y="${ny}"]`);
      if (ncell.dataset.occupiedBy) return true;
    }
    return false;
  }

  // Configurar celdas
  boardEl.querySelectorAll('.battleship-cell').forEach(cell => {
    cell.addEventListener('dragover', e => {
      e.preventDefault();
      if (!currentShip) return;
      clearPreview();

      const x = +cell.dataset.x, y = +cell.dataset.y;
      const cells = [];
      for (let i = 0; i < currentShip.size; i++) {
        const xi = orientation==='horizontal'? x+i : x;
        const yi = orientation==='vertical'?   y+i : y;
        if (xi>9||yi>9) { clearPreview(); return; }
        if (isBlocked(xi, yi)) { clearPreview(); return; }
        cells.push(boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`));
      }
      cells.forEach(c => {
        c.classList.remove('bg-blue-700');
        c.classList.add('bg-blue-900');
      });
      previewCells = cells;
    });

    cell.addEventListener('dragleave', () => {
      if (!currentShip) return;
      clearPreview();
    });

    cell.addEventListener('drop', () => {
      if (!currentShip || previewCells.length!==currentShip.size) return;

      // Registro del barco
      const shipRecord = {
        id: currentShip.id,
        size: currentShip.size,
        color: currentShip.color,
        el: currentShip.el,
        cells: previewCells.map(c=>[+c.dataset.x,+c.dataset.y])
      };

      // Colocar visualmente
      shipRecord.cells.forEach(([xi,yi]) => {
        const c = boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`);
        c.classList.remove('bg-blue-900');
        c.classList.add(shipRecord.color);
        c.dataset.occupiedBy = shipRecord.id;
      });

      placedShips.push(shipRecord);

      // ---- Aquí cambiamos el botón a gris ----
      shipRecord.el.draggable = false;
      shipRecord.el.classList.remove(shipRecord.color);
      shipRecord.el.classList.add('bg-gray-600','opacity-50','cursor-not-allowed');

      // Recolocar al hacer clic sobre sus celdas
      shipRecord.cells.forEach(([xi,yi]) => {
        const c = boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`);
        const handler = () => {
          // Quitar visual y datos
          shipRecord.cells.forEach(([x0,y0]) => {
            const cc = boardEl.querySelector(`.battleship-cell[data-x="${x0}"][data-y="${y0}"]`);
            cc.classList.remove(shipRecord.color);
            cc.classList.add('bg-blue-700');
            delete cc.dataset.occupiedBy;
          });
          // Reactivar botón y color original
          shipRecord.el.draggable = true;
          shipRecord.el.classList.remove('bg-gray-600','opacity-50','cursor-not-allowed');
          shipRecord.el.classList.add(shipRecord.color);
          // Eliminar del array
          const idx = placedShips.findIndex(s=>s.id===shipRecord.id);
          placedShips.splice(idx,1);
          // Desactivar “¡A jugar!” si falta alguno
          startBtn.disabled = true;
          statusEl.textContent = '';
        };
        c.addEventListener('click', handler);
      });

      clearPreview();
      currentShip = null;

      if (placedShips.length===shipEls.length) {
        startBtn.disabled = false;
        statusEl.textContent = '¡Listo para jugar!';
      }
    });
  });

  // Enviar setup
  startBtn.addEventListener('click', async () => {
    statusEl.textContent = '';
    try {
      const res = await fetch(setupUrl, {
        method:'POST',
        credentials:'same-origin',
        headers:{
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ships:placedShips.map(s=>({size:s.size,cells:s.cells}))})
      });
      if (!res.ok) {
        const err = await res.json().catch(()=>({message:res.statusText}));
        return statusEl.textContent = `Error: ${err.message}`;
      }
      const json = await res.json();
      if (json.start) {
        window.location.href = "{{ route('battleship.play',['battleship_game'=>$battleship_game->id]) }}";
      } else {
        statusEl.textContent = 'Esperando a que el rival coloque sus barcos…';
      }
    } catch(e) {
      console.error(e);
      statusEl.textContent = 'Error de red, inténtalo de nuevo.';
    }
  });
});
</script>
@endpush