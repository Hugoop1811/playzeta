@extends('layout')

@section('title', 'Speed Click - Mejores Puntuaciones')

@section('content')

<style>
   .leaderboard-container {
    text-align: center;
    padding: 20px;
}

.leaderboard-title {
    font-size: 28px;
    margin-bottom: 20px;
    color: #f1f1f1; /* más claro para que se vea en fondo oscuro */
}

.leaderboard-table {
    width: 100%;
    max-width: 800px;
    margin: 0 auto;
    border-collapse: collapse;
    font-family: Arial, sans-serif;
    font-size: 16px;
    color: #f1f1f1; /* texto claro por defecto */
}

.leaderboard-table thead {
    background-color: #3498db;
    color: white;
}

.leaderboard-table th, .leaderboard-table td {
    padding: 12px 15px;
    border: 1px solid #444; /* borde más oscuro que se vea en fondo oscuro */
    text-align: center;
}

.leaderboard-table tbody tr:nth-child(even) {
    background-color: #2c2c2c; /* gris oscuro, no blanco */
    color: #f1f1f1; /* asegurar que el texto sea visible */
}

.leaderboard-table tbody tr:nth-child(odd) {
    background-color: #1e1e1e; /* un poco más oscuro */
    color: #f1f1f1; /* asegurar que el texto sea visible */
}

.leaderboard-table tbody tr:hover {
    background-color: #3a3a3a;
}

.btn-custom {
    display: inline-block;
    padding: 10px 15px;
    background-color: #3498db;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    margin: 10px;
    transition: background-color 0.3s ease, transform 0.1s ease;
}

.btn-custom:hover {
    background-color: #2980b9;
    transform: scale(1.05);
}

.btn-custom:active {
    background-color: #2471a3;
    transform: scale(0.98);
}

</style>

<div class="leaderboard-container">
    <h1 class="leaderboard-title">🏆 Mejores Puntuaciones - Speed Click</h1>

    <table class="leaderboard-table">
        <thead>
            <tr>
                <th>Posición</th>
                <th>Nombre</th>
                <th>Tiempo de Reacción (ms)</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topScores as $index => $score)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $score->user_name }}</td>
                    <td>{{ $score->reaction_time_ms }}</td>
                    <td>{{ \Carbon\Carbon::parse($score->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        <a href="{{ url('/speedclick') }}" class="btn-custom">Volver al Juego</a>
    </div>
</div>

@endsection
