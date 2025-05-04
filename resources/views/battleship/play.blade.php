{{-- resources/views/battleship/play.blade.php --}}
@extends('layout')

@section('content')
<div class="battleship-container p-6">
  <h1>Hundir la Flota</h1>
  <div id="player-board"></div>
  <div id="opponent-board"></div>
  <div id="status"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Original play JS ...
});
</script>
@endpush
