@extends('layouts.app')

@section('content')
<div class="container">
  <h1>Mis partidas de Hundir la Flota</h1>

  <a href="{{ route('battleship.create') }}" class="btn btn-primary mb-4">
    Nueva partida
  </a>

  @if($games->isEmpty())
    <p>No tienes ninguna partida todavía.</p>
  @else
    <table class="table table-striped">
      <thead>
        <tr>
          <th>Modo</th>
          <th>Dificultad</th>
          <th>Estado</th>
          <th>Creada</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        @foreach($games as $game)
        <tr>
          <td>{{ $game->mode }}</td>
          <td>{{ $game->mode === 'IA' ? $game->difficulty : '—' }}</td>
          <td>{{ ucfirst($game->status) }}</td>
          <td>{{ $game->created_at->format('Y-m-d H:i') }}</td>
          <td>
            <a href="{{ route('battleship.show', $game) }}" class="btn btn-sm btn-outline-secondary">
              {{ $game->status === 'setup' ? 'Colocar barcos' : 'Continuar' }}
            </a>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif
</div>
@endsection