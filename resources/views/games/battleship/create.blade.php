@extends('layout')

@section('content')
<div class="container">
  <h1>Crear nueva partida de Hundir la Flota</h1>

  <form action="{{ route('battleship.store') }}" method="POST">
    @csrf

    <div class="mb-3">
      <label class="form-label">Modo de juego</label><br>
      <label>
        <input type="radio" name="mode" value="IA" checked
               x-data x-on:change="document.getElementById('difficulty').disabled = false">
        Versus IA
      </label>
      <label class="ms-3">
        <input type="radio" name="mode" value="PVP"
               x-data x-on:change="document.getElementById('difficulty').disabled = true">
        Multijugador
      </label>
    </div>

    <div class="mb-3">
      <label for="difficulty" class="form-label">Dificultad IA</label>
      <select name="difficulty" id="difficulty" class="form-select">
        <option value="easy">Fácil</option>
        <option value="medium">Medio</option>
        <option value="hard">Difícil</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary">Crear partida</button>
    <a href="{{ route('battleship.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
  </form>
</div>
@endsection
