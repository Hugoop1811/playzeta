{{-- resources/views/games/battleship/index.blade.php --}}
@extends('layout')

@section('content')
<div class="container mx-auto p-6">

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Mis Partidas de Hundir la Flota</h1>
    <a href="{{ route('battleship.create') }}"
       class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
      Nueva Partida
    </a>
  </div>

  @guest
    <div class="p-4 bg-yellow-600 text-white rounded">
      Para ver tus partidas y registrar nuevas,
      <a href="{{ route('login') }}" class="underline font-semibold">inicia sesión</a>.
    </div>
  @endguest

  @auth
    @if($games->isEmpty())
      <p class="text-gray-500">No tienes ninguna partida aún.</p>
    @else
      <div class="space-y-4">
        @foreach($games as $game)
          @php
            // Decidir las rutas según modo IA vs PVP
            $setupRoute = $game->mode === 'IA'
              ? route('battleship.ia.setup.view', $game)
              : route('battleship.pvp.setup.view', $game);
            $playRoute  = $game->mode === 'IA'
              ? route('battleship.ia.play', $game)
              : route('battleship.pvp.play', $game);
          @endphp

          <div class="flex justify-between items-center p-4 bg-gray-800 rounded">
            <div>
              <span class="font-medium">#{{ $game->id }}</span>
              <span class="text-sm text-gray-400">
                {{ $game->mode === 'IA' ? 'Vs IA' : 'PVP' }}
                @if($game->mode === 'IA')
                  ({{ ucfirst($game->difficulty) }})
                @endif
                — {{ ucfirst($game->status) }}
              </span>
            </div>
            <div class="space-x-2">
              @if($game->status === 'setup')
                <a href="{{ $setupRoute }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                  Continuar Setup
                </a>
              @else
                <a href="{{ $playRoute }}"
                   class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                  Jugar
                </a>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif
  @endauth

</div>
@endsection
