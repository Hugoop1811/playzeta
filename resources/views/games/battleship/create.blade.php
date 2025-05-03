<form action="{{ route('battleship.store') }}" method="POST">
  @csrf
  <label>
    <input type="radio" name="mode" value="IA" checked> Versus IA
  </label>
  <select name="difficulty">
    <option value="easy">Fácil</option>
    <option value="medium">Medio</option>
    <option value="hard">Difícil</option>
  </select>
  <label>
    <input type="radio" name="mode" value="PVP"> Multijugador
  </label>
  <button type="submit">Crear partida</button>
</form>