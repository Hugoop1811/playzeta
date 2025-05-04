{{-- resources/views/games/battleship/play.blade.php --}}
@extends('layout')

@section('content')
@php
    $playerBoard = $battleship_game->boards->firstWhere('owner','player');
    $oppBoard    = $battleship_game->boards->firstWhere('owner','opponent');
@endphp

<div class="battleship-container p-6">
  <h1 class="text-3xl font-bold mb-4">Hundir la Flota</h1>
  <p class="mb-4">Dispara clicando en las casillas del tablero rival.</p>

  <div class="flex">
    {{-- TU TABLERO --}}
    <div>
      <h2 class="text-xl mb-2">Tu tablero</h2>
      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-1">
        @php
          $hitsP  = $playerBoard->hits ?? [];
          $shipsP = $playerBoard->ships ?? [];
        @endphp
        @for ($y = 0; $y < 10; $y++)
          @for ($x = 0; $x < 10; $x++)
            @php
              $occupied = collect($shipsP)
                ->contains(fn($s) => in_array([$x,$y], $s['cells']));
              $wasHit = in_array([$x,$y], $hitsP);
              if ($wasHit) {
                $color = $occupied ? 'bg-red-600' : 'bg-blue-400';
              } else {
                $color = $occupied ? 'bg-gray-600' : 'bg-gray-800';
              }
            @endphp
            <div
              class="w-8 h-8 border border-gray-600 {{ $color }}"
              data-x="{{ $x }}" data-y="{{ $y }}"
            ></div>
          @endfor
        @endfor
      </div>
    </div>

    {{-- TABLERO RIVAL --}}
    <div class="ml-8">
      <h2 class="text-xl mb-2">Tablero rival</h2>
      <div id="opponent-board" class="grid grid-rows-10 grid-cols-10 gap-1">
        @php
          $hitsR  = $oppBoard->hits ?? [];
          $shipsR = $oppBoard->ships ?? [];
        @endphp
        @for ($y = 0; $y < 10; $y++)
          @for ($x = 0; $x < 10; $x++)
            @php
              if (in_array([$x,$y], $hitsR)) {
                $hitShip = collect($shipsR)
                  ->contains(fn($s) => in_array([$x,$y], $s['cells']));
                $color = $hitShip ? 'bg-red-600' : 'bg-blue-400';
                $clickClass = '';
              } else {
                $color = 'bg-gray-800 hover:bg-gray-700 cursor-pointer cell-clickable';
                $clickClass = 'cell-clickable';
              }
            @endphp
            <div
              class="w-8 h-8 border border-gray-600 {{ $color }} {{ $clickClass }}"
              data-x="{{ $x }}" data-y="{{ $y }}"
            ></div>
          @endfor
        @endfor
      </div>
    </div>
  </div>

  <div id="status" class="mt-4 text-lg font-semibold"></div>
  <div id="post-game" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const moveUrl        = "{{ route('battleship.move', $battleship_game) }}";
  const newGameUrl     = "{{ route('battleship.create') }}";
  const playerBoardEl  = document.getElementById('player-board');
  const opponentBoard  = document.getElementById('opponent-board');
  const statusEl       = document.getElementById('status');
  const postGameEl     = document.getElementById('post-game');
  const oppShips       = @json($oppBoard->ships);
  let currentTurn      = "{{ $battleship_game->turn }}";
  const mode           = "{{ $battleship_game->mode }}";

  function enableClicks() {
    if (mode === 'PVP' && currentTurn !== 'player') return;
    opponentBoard.querySelectorAll('.cell-clickable').forEach(cell => {
      cell.addEventListener('click', handleClick);
    });
  }

  async function handleClick(e) {
    if (mode === 'PVP' && currentTurn !== 'player') return;

    const cell = e.currentTarget;
    const x = +cell.dataset.x, y = +cell.dataset.y;

    // Optimistic UI: pinta de agua al instante
    cell.classList.replace('bg-gray-800', 'bg-blue-400');
    statusEl.textContent = 'Agua';
    statusEl.className = 'mt-4 text-lg font-semibold text-blue-500';

    // Bloquea más clicks
    opponentBoard.style.pointerEvents = 'none';
    postGameEl.innerHTML = '';

    try {
      const res = await fetch(moveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type':     'application/json',
          'Accept':           'application/json',
          'X-CSRF-TOKEN':     '{{ csrf_token() }}',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ x, y })
      });
      const data = await res.json();

      // Actualiza turno
      currentTurn = data.turn;

      // Ajusta si fue realmente “tocado”
      if (data.resultPlayer === 'tocado') {
        cell.classList.replace('bg-blue-400', 'bg-red-600');
        statusEl.textContent = 'Tocado';
        statusEl.className = 'mt-4 text-lg font-semibold text-red-600';
      }
      // O “hundido”: pinta todo el barco
      else if (data.resultPlayer === 'hundido') {
        const ship = oppShips.find(s =>
          s.cells.some(c => c[0] === x && c[1] === y)
        );
        if (ship) {
          ship.cells.forEach(([sx,sy]) => {
            const el = opponentBoard.querySelector(
              `.w-8.h-8.border[data-x="${sx}"][data-y="${sy}"]`
            );
            el.classList.remove(
              'bg-gray-800','bg-blue-400','bg-red-600',
              'hover:bg-gray-700','cursor-pointer'
            );
            el.classList.add('bg-green-500');
          });
        } else {
          cell.classList.replace('bg-blue-400','bg-green-500');
        }
        statusEl.textContent = 'Tocado y hundido';
        statusEl.className = 'mt-4 text-lg font-semibold text-green-600';
      }

      // Si hay IA, pinta su disparo
      if (mode === 'IA' && data.coordsAI) {
        const [ax, ay] = data.coordsAI;
        const pe = playerBoardEl.querySelector(
          `.w-8.h-8.border[data-x="${ax}"][data-y="${ay}"]`
        );
        pe.classList.replace('bg-gray-800',
          data.resultAI === 'agua' ? 'bg-blue-400' : 'bg-red-600'
        );
      }

      // Fin de partida
      if (data.gameOver) {
        statusEl.textContent = data.winner === 'player'
          ? '¡Has ganado! 🎉'
          : '¡Has perdido! 💥';
        statusEl.className = 'mt-4 text-lg font-semibold ' +
          (data.winner === 'player' ? 'text-green-600' : 'text-red-600');

        const btn = document.createElement('button');
        btn.textContent = 'Jugar otra partida';
        btn.className = 'mt-4 px-4 py-2 bg-blue-600 text-white rounded';
        btn.addEventListener('click', () => window.location.href = newGameUrl);
        postGameEl.appendChild(btn);
      } else {
        // reactiva clicks
        opponentBoard.style.pointerEvents = '';
        enableClicks();
      }

    } catch (err) {
      console.error(err);
      statusEl.textContent = 'Error, inténtalo de nuevo';
      statusEl.className = 'mt-4 text-lg font-semibold text-red-600';
      cell.classList.replace('bg-blue-400','bg-gray-800');
      opponentBoard.style.pointerEvents = '';
      enableClicks();
    }
  }

  // Polling para PVP: consulta cada 2s si no es tu turno
  if (mode === 'PVP') {
    setInterval(async () => {
      if (currentTurn === 'player') return;
      const res = await fetch("{{ route('battleship.state', $battleship_game) }}", {
        headers: { 'Accept': 'application/json' }
      });
      const st = await res.json();
      currentTurn = st.turn;
      if (st.turn === 'player') {
        const last = st.playerHits.slice(-1)[0];
        if (last) {
          const [x,y] = last;
          const cell = document.querySelector(
            `#player-board .w-8.h-8.border[data-x="${x}"][data-y="${y}"]`
          );
          const wasHit = st.playerShips
            .some(s => s.cells.some(c=>c[0]===x&&c[1]===y));
          cell.classList.replace('bg-gray-800',
            wasHit ? 'bg-red-600' : 'bg-blue-400'
          );
        }
        opponentBoard.style.pointerEvents = '';
        enableClicks();
      }
    }, 2000);
  }

  enableClicks();
});
</script>
@endpush