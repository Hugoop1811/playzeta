{{-- resources/views/games/battleship/play.blade.php --}}
@extends('layout')

@section('content')
@php
  $hitsP  = $playerBoard->hits  ?? [];
  $shipsP = $playerBoard->ships ?? [];
  $hitsO  = $oppBoard->hits     ?? [];
@endphp

<div class="grid justify-center mx-auto p-6 min-h-screen">
  <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Hundir la Flota</h1>
    <p id="info" class="mb-4 text-gray-300">
      Turno: <span id="turn" class="font-semibold text-white">{{ ucfirst($battleship_game->turn) }}</span>
    </p>

    <div class="flex space-x-12">
      {{-- Tu tablero --}}
      <div>
        <h2 class="text-lg font-medium text-white mb-2">Tu tablero</h2>
        <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-[0.05rem]">
          @for($y=0;$y<10;$y++)
            @for($x=0;$x<10;$x++)
              @php
                $occ = collect($shipsP)->contains(fn($s)=> in_array([$x,$y], $s['cells']));
                $hit = in_array([$x,$y], $hitsP);
                $bg  = $occ
                     ? ($hit?'bg-red-600':'bg-gray-500')
                     : ($hit?'bg-blue-400':'bg-blue-700');
              @endphp
              <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
            @endfor
          @endfor
        </div>
      </div>

      {{-- Tablero rival --}}
      <div>
        <h2 class="text-lg font-medium text-white mb-2">Tablero rival</h2>
        <div id="opponent-board" class="grid grid-rows-10 grid-cols-10 gap-[0.05rem]">
          @for($y=0;$y<10;$y++)
            @for($x=0;$x<10;$x++)
              @php
                $hit     = in_array([$x,$y], $hitsO);
                $hitShip = collect($oppBoard->ships)
                            ->contains(fn($s)=> in_array([$x,$y], $s['cells']));
                $bg = $hit
                    ? ($hitShip?'bg-red-600':'bg-blue-400')
                    : 'bg-gray-700 hover:bg-gray-600 cursor-pointer cell-clickable';
              @endphp
              <div
                class="w-10 h-10 {{ $bg }}"
                data-x="{{ $x }}" data-y="{{ $y }}"
              ></div>
            @endfor
          @endfor
        </div>
      </div>
    </div>

    <div id="status" class="mt-6 text-center text-lg text-yellow-400"></div>
    <div class="mt-4 text-center">
      <a href="{{ route('battleship.index') }}"
         class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-500">
        Volver a partidas
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const moveUrl       = "{{ route('battleship.move', $battleship_game) }}";
  const oppBoardEl    = document.getElementById('opponent-board');
  const playerBoardEl = document.getElementById('player-board');
  const statusEl      = document.getElementById('status');
  const turnEl        = document.getElementById('turn');

  function enableClicks() {
    oppBoardEl.querySelectorAll('.cell-clickable').forEach(cell => {
      cell.addEventListener('click', handleClick, { once: true });
    });
  }

  async function handleClick(e) {
    const cell = e.currentTarget;
    const x = +cell.dataset.x, y = +cell.dataset.y;

    // Desactivar solo esta celda
    cell.classList.remove('hover:bg-gray-600','cursor-pointer','cell-clickable');
    // Bloquear tablero hasta respuesta
    oppBoardEl.style.pointerEvents = 'none';
    statusEl.textContent = '';

    try {
      const res  = await fetch(moveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type':'application/json',
          'Accept':'application/json',
          'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ x,y })
      });
      const data = await res.json();
      if (!res.ok) {
        statusEl.textContent = `Error: ${data.message}`;
      } else {
        // 1) Pintar hundidos completos
        if (Array.isArray(data.sunkCells) && data.sunkCells.length) {
          data.sunkCells.forEach(([sx,sy]) => {
            const c = oppBoardEl.querySelector(
              `[data-x="${sx}"][data-y="${sy}"]`
            );
            c.classList.replace('bg-gray-700','bg-green-500');
            c.classList.remove('hover:bg-gray-600','cursor-pointer','cell-clickable');
          });
          statusEl.textContent = 'Tocado y hundido';
          statusEl.className = 'mt-6 text-center text-lg text-green-500';
        }
        // 2) Pintar tocado simple
        else if (data.resultPlayer === 'tocado') {
          cell.classList.replace('bg-gray-700','bg-red-600');
          statusEl.textContent = 'Tocado';
          statusEl.className = 'mt-6 text-center text-lg text-red-600';
        }
        // 3) Pintar agua
        else {
          cell.classList.replace('bg-gray-700','bg-blue-400');
          statusEl.textContent = 'Agua';
          statusEl.className = 'mt-6 text-center text-lg text-blue-400';
        }

        // 4) IA dispara
        if (data.coordsAI) {
          const [ax,ay] = data.coordsAI;
          const pe = playerBoardEl.querySelector(
            `[data-x="${ax}"][data-y="${ay}"]`
          );
          if (data.resultAI === 'agua') {
            pe.classList.replace('bg-blue-700','bg-blue-400');
          } else {
            pe.classList.replace('bg-gray-500','bg-red-600');
          }
        }

        // 5) Turno y fin
        turnEl.textContent = data.turn.charAt(0).toUpperCase() + data.turn.slice(1);
        if (data.gameOver) {
          statusEl.textContent = data.winner==='player'
            ? '¡Has ganado! 🎉'
            : '¡Has perdido! 💥';
          oppBoardEl.style.pointerEvents = 'none';
        } else {
          oppBoardEl.style.pointerEvents = '';
          enableClicks();
        }
      }
    } catch(err) {
      console.error(err);
      statusEl.textContent = 'Error de red, inténtalo de nuevo.';
      oppBoardEl.style.pointerEvents = '';
      enableClicks();
    }
  }

  enableClicks();
});
</script>
@endpush