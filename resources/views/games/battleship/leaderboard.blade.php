@extends('layout')

@section('content')
<div class="container">
  <h1>Clasificación de Hundir la Flota</h1>

  @if($scores->isEmpty())
    <p>Aún no hay resultados registrados.</p>
  @else
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Jugador</th>
          <th>Puntos</th>
          <th>Duración (s)</th>
          <th>Fecha</th>
        </tr>
      </thead>
      <tbody>
        @foreach($scores as $i => $score)
        <tr>
          <td>{{ $i + 1 }}</td>
          <td>{{ $score->user->name }}</td>
          <td>{{ $score->score }}</td>
          <td>{{ $score->duration }}</td>
          <td>{{ $score->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  <a href="{{ route('battleship.index') }}" class="btn btn-secondary mt-3">
    ← Volver a mis partidas
  </a>
</div>
@endsection