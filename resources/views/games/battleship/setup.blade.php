@extends('layout')

@section('content')
<div class="battleship-container p-6">
  <h1 class="text-3xl font-bold mb-4">Coloca tus barcos</h1>
  <p class="mb-6">Arrastra y sitúa tus barcos en el tablero de 10×10. Cuando termines, haz clic en “¡A jugar!”.</p>

  <div class="flex">
    {{-- Tablero 10×10 --}}
    <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-1">
      @for ($y = 0; $y < 10; $y++)
        @for ($x = 0; $x < 10; $x++)
          <div
            class="battleship-cell w-8 h-8 border border-gray-600 bg-gray-800 hover:bg-gray-700"
            data-x="{{ $x }}"
            data-y="{{ $y }}"
          ></div>
        @endfor
      @endfor
    </div>

    {{-- Paleta de barcos --}}
    <div class="ml-8 flex flex-col">
      @foreach ([
        ['size'=>5,'label'=>'Portaaviones'],
        ['size'=>4,'label'=>'Acorazado'],
        ['size'=>3,'label'=>'Submarino'],
        ['size'=>3,'label'=>'Crucero'],
        ['size'=>2,'label'=>'Destructor'],
      ] as $ship)
        <div
          draggable="true"
          data-size="{{ $ship['size'] }}"
          class="ship mb-3 p-2 bg-gray-700 rounded cursor-grab hover:bg-gray-600 active:cursor-grabbing"
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
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const boardEl  = document.getElementById('player-board');
  const ships    = Array.from(document.querySelectorAll('.ship'));
  const startBtn = document.getElementById('start-game');
  const placed   = [];
  let currentShip = null,
      orientation = 'horizontal';
  ships.forEach(ship => {
    ship.addEventListener('dragstart', () => {
      currentShip = { size: +ship.dataset.size, el: ship };
    });
  });
      });

  boardEl.querySelectorAll('.battleship-cell').forEach(cell => {
    cell.addEventListener('dragover', e => e.preventDefault());
    cell.addEventListener('drop', () => {
      const x = +cell.dataset.x, y = +cell.dataset.y, size = currentShip.size;
      const cells = [];
      for (let i = 0; i < size; i++) {
        const xi = orientation === 'horizontal' ? x + i : x;
        const yi = orientation === 'vertical'   ? y + i : y;
        if (xi > 9 || yi > 9) return alert('Fuera de tablero');
        cells.push([xi, yi]);
      }
      cells.forEach(([xi, yi]) => {
        const el = boardEl.querySelector(`.battleship-cell[data-x="${xi}"][data-y="${yi}"]`);
        el.classList.remove('bg-gray-800','hover:bg-gray-700');
        el.classList.add('bg-gray-500');
      });
      currentShip.draggable = false;
      placed.push({ size, cells });
      if (placed.length === ships.length) startBtn.disabled = false;
    });
  });

  startBtn.addEventListener('click', () => {
    fetch("{{ route('battleship.setup', $battleship_game) }}", {
      method: 'POST',
      headers: {
        'Content-Type':'application/json',
        'X-CSRF-TOKEN':'{{ csrf_token() }}'
      },
      body: JSON.stringify({ ships: placed })
    })
    .then(r => r.json())
    .then(json => {
      if (json.ok) window.location.href = "{{ route('battleship.show', $battleship_game) }}";
      else alert('Error guardando posiciones');
    });
});
</script>
@endpush