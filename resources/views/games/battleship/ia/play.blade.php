{{-- resources/views/games/battleship/play.blade.php --}}
@extends('layout')

@section('content')

  <audio id="bg-music" src="{{ asset('audio/MusicaCombateBattleship.mp3') }}" loop preload="auto"></audio>
  <audio id="cannon-sound" src="{{ asset('audio/DisparoCannon.mp3') }}" preload="auto"></audio>
  <audio id="impact-sound" src="{{ asset('audio/ImpactoBala.mp3') }}" preload="auto"></audio>
  <audio id="water-sound" src="{{ asset('audio/CaidaAlAgua.mp3') }}" preload="auto"></audio>
  <audio id="hundido-sound" src="{{ asset('audio/BarcoHundido.mp3') }}" preload="auto"></audio>

  {{-- Toggle música --}}
  @php
    $volDecimal = session('battleship_bg_volume', 0.3);
    $volPercent = (int) round($volDecimal * 100);
  @endphp
  <div class="absolute bottom-4 right-4">
    <button id="music-toggle" class="px-3 py-1 bg-gray-700 text-white rounded">🔊</button>
    <input id="volume-slider" type="range" min="0" max="100" step="1" value="{{ $volPercent }}" class="h-1 w-24">
  </div>

  @php
    $hitsP = $playerBoard->hits ?? [];
    $shipsP = $playerBoard->ships ?? [];
    $hitsO = $oppBoard->hits ?? [];
    $level = $battleship_game->difficulty;
    $oppShips = $oppBoard->ships ?? [];
  @endphp

  <div class="grid justify-center mx-auto p-6 min-h-screen">
    <div class="bg-gray-900 p-6">
    <h1 class="text-2xl font-bold mb-4 text-white">Hundir la Flota</h1>
    <p id="info" class="mb-4 text-gray-300">
      Modo: <span class="font-semibold text-white">{{ ucfirst($level) }}</span>
    </p>
    @php $diff = strtolower($battleship_game->difficulty) @endphp
    <div class="flex items-center mb-4">
      <p class="text-gray-300 mr-4">
      Dificultad: <strong>{{ ucfirst($diff) }}</strong>
      </p>
      @if($diff === 'hard')
      <div id="timer" class="text-white font-mono text-lg bg-gray-700 px-2 py-1 rounded">
      5
      </div>
    @endif
    </div>

    <div class="flex gap-[41px]">
      {{-- Tu tablero --}}
      <div>
      <h2 class="text-lg font-medium text-white mb-2">Tu tablero</h2>
      <div id="player-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $occ = collect($shipsP)->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $hit = in_array([$x, $y], $hitsP);
      $bg = $occ
      ? ($hit ? 'bg-red-600' : 'bg-gray-500')
      : ($hit ? 'bg-blue-400' : 'bg-blue-700');
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>

      {{-- Tablero rival --}}
      <div margin-left="45px">
      <h2 class="text-lg font-medium text-white mb-2">Tablero rival</h2>
      <div id="opponent-board" class="grid grid-rows-10 grid-cols-10 gap-[0.06rem]">
        @for($y = 0; $y < 10; $y++)
        @for($x = 0; $x < 10; $x++)
        @php
      $hit = in_array([$x, $y], $hitsO);
      $hitShip = collect($oppBoard->ships)
      ->contains(fn($s) => in_array([$x, $y], $s['cells']));
      $bg = $hit
      ? ($hitShip ? 'bg-red-600' : 'bg-blue-400')
      : 'bg-gray-700 hover:bg-gray-600 cursor-pointer cell-clickable';
      @endphp
        <div class="w-10 h-10 {{ $bg }}" data-x="{{ $x }}" data-y="{{ $y }}"></div>
      @endfor
      @endfor
      </div>
      </div>
    </div>

    <div id="status" class="mt-6 text-center text-lg text-yellow-400"></div>
    <div class="mt-4 text-center">
      <a href="{{ route('battleship.index') }}" class="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-500">
      Volver a partidas
      </a>
    </div>
    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    // URLs y configuración
    const moveUrl = "{{ route('battleship.ia.move', $battleship_game) }}";
    const difficulty = "{{ strtolower($battleship_game->difficulty) }}";
    const oppShips = @json($oppBoard->ships ?? []);

    // Elementos del DOM
    const oppBoardEl = document.getElementById('opponent-board');
    const playerBoardEl = document.getElementById('player-board');
    const statusEl = document.getElementById('status');
    const timerEl = document.getElementById('timer');

    // Audio
    const bgMusic = document.getElementById('bg-music');
    const cannonSound = document.getElementById('cannon-sound');
    const waterSound = document.getElementById('water-sound');
    const impactSound = document.getElementById('impact-sound');
    const hundidoSound = document.getElementById('hundido-sound');

    const volumeSlider = document.getElementById('volume-slider');
    const musicToggle = document.getElementById('music-toggle');

    // Inicializar volumen
    bgMusic.volume = volumeSlider.value / 100;
    // Solo si la página fue recargada (F5 o Ctrl+R)
    bgMusic.play().catch(() => { }); // Autoplay puede fallar si el usuario no ha interactuado
    const navType = performance.getEntriesByType("navigation")[0]?.type;
    if (navType === 'reload') {
      function startMusicOnce() {
      bgMusic.play().catch(() => { });
      document.removeEventListener('click', startMusicOnce);
      }
      document.addEventListener('click', startMusicOnce);
    }

    document.addEventListener('click', startMusicOnce);
    musicToggle.addEventListener('click', () => {
      if (bgMusic.paused) {
      bgMusic.play();
      musicToggle.textContent = '🔊';
      } else {
      bgMusic.pause();
      musicToggle.textContent = '🔇';
      }
    });

    volumeSlider.addEventListener('input', async () => {
      const vol = volumeSlider.value / 100;
      bgMusic.volume = vol;
      try {
      await fetch('/api/battleship/audio', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ volume: vol })
      });
      } catch (e) {
      console.warn('No se pudo guardar el volumen.');
      }
    });

    let countdown, timeLeft;

    function enableClicks() {
      oppBoardEl.querySelectorAll('.cell-clickable')
      .forEach(cell => cell.addEventListener('click', handleClick, { once: true }));
    }

    function clearTimer() {
      if (countdown) clearInterval(countdown);
      countdown = null;
    }

    function startTimer() {
      if (difficulty !== 'hard') return;
      clearTimer();
      timeLeft = 5;
      timerEl.textContent = timeLeft;
      countdown = setInterval(() => {
      timeLeft--;
      timerEl.textContent = timeLeft >= 0 ? timeLeft : '0';
      if (timeLeft < 0) {
        clearTimer();
        handleTimeout();
      }
      }, 1000);
    }

    async function handleTimeout() {
      statusEl.textContent = '¡Tiempo agotado! Turno perdido…';
      oppBoardEl.style.pointerEvents = 'none';

      try {
      const res = await fetch(moveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ skip: true })
      });
      const data = await res.json();

      // Pintar disparo IA
      if (data.coordsAI) {
        cannonSound.currentTime = 0; cannonSound.play();
        const [ax, ay] = data.coordsAI;
        const pe = playerBoardEl.querySelector(`[data-x="${ax}"][data-y="${ay}"]`);
        if (data.resultAI === 'agua') {
        waterSound.currentTime = 0; waterSound.play();
        pe.classList.replace('bg-blue-700', 'bg-blue-400');
        } else if (data.resultAI === 'tocado') {
        impactSound.currentTime = 0; impactSound.play();
        pe.classList.replace('bg-gray-500', 'bg-red-600');
        } else {
        hundidoSound.currentTime = 0; hundidoSound.play();
        pe.classList.replace('bg-gray-500', 'bg-green-500');
        }
      }

      // Reactivar turno del jugador
      statusEl.textContent = '';
      oppBoardEl.style.pointerEvents = '';
      enableClicks();
      startTimer();
      } catch (err) {
      console.error(err);
      statusEl.textContent = 'Error de timeout.';
      }
    }

    async function handleClick(e) {
      clearTimer();
      const cell = e.currentTarget;
      const x = +cell.dataset.x, y = +cell.dataset.y;

      // Disparo jugador
      cannonSound.currentTime = 0; cannonSound.play();
      cell.classList.remove('hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
      oppBoardEl.style.pointerEvents = 'none';
      statusEl.textContent = '';

      let data;
      try {
      const res = await fetch(moveUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ x, y })
      });
      data = await res.json();
      if (!res.ok) {
        statusEl.textContent = `Error: ${data.message}`;
        return;
      }
      } catch (err) {
      console.error(err);
      statusEl.textContent = 'Error de red.';
      return;
      }

      // Pintar resultado jugador
      if (Array.isArray(data.sunkCells) && data.sunkCells.length) {
      hundidoSound.currentTime = 0; hundidoSound.play();
      data.sunkCells.forEach(([sx, sy]) => {
        const c = oppBoardEl.querySelector(`[data-x="${sx}"][data-y="${sy}"]`);
        c.classList.replace('bg-gray-700', 'bg-green-500');
        c.classList.remove('hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
      });
      statusEl.textContent = 'Tocado y hundido';
      statusEl.className = 'mt-6 text-center text-lg text-green-500';
      } else if (data.resultPlayer === 'tocado') {
      impactSound.currentTime = 0; impactSound.play();
      cell.classList.replace('bg-gray-700', 'bg-red-600');
      statusEl.textContent = 'Tocado';
      statusEl.className = 'mt-6 text-center text-lg text-red-600';
      } else {
      waterSound.currentTime = 0; waterSound.play();
      cell.classList.replace('bg-gray-700', 'bg-blue-400');
      statusEl.textContent = 'Agua';
      statusEl.className = 'mt-6 text-center text-lg text-blue-400';
      }

      // Si ganó el jugador
      if (data.gameOver && data.winner === 'player') {
      statusEl.textContent = '¡Has ganado! 🎉';
      oppBoardEl.style.pointerEvents = 'none';
      return;
      }

      // IA piensa…
      statusEl.textContent = 'IA está pensando…';
      const delay = 800 + Math.random() * 1200;
      setTimeout(() => {
      // Disparo IA
      cannonSound.currentTime = 0; cannonSound.play();
      if (data.coordsAI) {
        const [ax, ay] = data.coordsAI;
        const pe = playerBoardEl.querySelector(`[data-x="${ax}"][data-y="${ay}"]`);
        if (data.resultAI === 'agua') {
        waterSound.currentTime = 0; waterSound.play();
        pe.classList.replace('bg-blue-700', 'bg-blue-400');
        } else if (data.resultAI === 'tocado') {
        impactSound.currentTime = 0; impactSound.play();
        pe.classList.replace('bg-gray-500', 'bg-red-600');
        } else {
        hundidoSound.currentTime = 0; hundidoSound.play();
        pe.classList.replace('bg-gray-500', 'bg-green-500');
        }
      }

      // Si ganó la IA
      if (data.gameOver && data.winner === 'opponent') {
        statusEl.textContent = '¡Has perdido! 💥';
        oppBoardEl.style.pointerEvents = 'none';
        oppShips.forEach(ship => {
        ship.cells.forEach(([sx, sy]) => {
          const c = oppBoardEl.querySelector(`[data-x="${sx}"][data-y="${sy}"]`);
          if (c.classList.contains('bg-gray-700')) {
          c.classList.replace('bg-gray-700', 'bg-gray-500');
          }
          c.classList.remove('hover:bg-gray-600', 'cursor-pointer', 'cell-clickable');
        });
        });
        return;
      }

      // Vuelve turno jugador
      statusEl.textContent = '';
      oppBoardEl.style.pointerEvents = '';
      enableClicks();
      startTimer();
      }, delay);
    }

    // Inicialización
    enableClicks();
    if (difficulty === 'hard') startTimer();
    });
  </script>
@endpush