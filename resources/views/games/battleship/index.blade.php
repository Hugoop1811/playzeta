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

  @if($games->isEmpty())
    <p class="text-gray-500">No tienes ninguna partida aún.</p>
  @else
    <div class="space-y-4">
      @foreach($games as $game)
        <div class="flex justify-between items-center p-4 bg-gray-800 rounded">
          <div>
            <span class="font-medium">#{{ $game->id }}</span>
            <span class="text-sm text-gray-400">
              {{ ucfirst(strtolower($game->mode)) }}
              @if($game->mode==='IA') ({{ ucfirst($game->difficulty) }}) @endif —
              {{ ucfirst($game->status) }}
            </span>
          </div>
          <div class="space-x-2">
            @if($game->status === 'setup')
              <a href="{{ route('battleship.setup.view', $game) }}"
                 class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Continuar Setup
              </a>
            @else
              <a href="{{ route('battleship.play', $game) }}"
                 class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Jugar
              </a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection