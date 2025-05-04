{{-- resources/views/battleship/setup.blade.php --}}
@extends('layout')

@section('content')
<div class="battleship-container p-6">
  <h1>Coloca tus barcos</h1>
  <div id="player-board"></div>
  <button id="start-game" disabled>¡A jugar!</button>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Original setup JS ...
});
</script>
@endpush
