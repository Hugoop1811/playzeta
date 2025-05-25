@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto py-10 text-white">
    <h2 class="text-3xl font-bold text-center mb-6 text-purple-400">🏆 Top 50 - Wordle Contrarreloj</h2>

    <div class="bg-gray-800 shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full text-center">
            <thead class="bg-gray-700 text-indigo-300">
                <tr>
                    <th class="px-4 py-2">Posición</th>
                    <th class="px-4 py-2">Jugador</th>
                    <th class="px-4 py-2">Puntuación</th>
                    <th class="px-4 py-2">Fecha</th>
                </tr>
            </thead>
            <tbody class="bg-gray-900 text-white">
                @foreach($topScores as $index => $entry)
                    <tr class="{{ $index % 2 == 0 ? 'bg-gray-800' : 'bg-gray-900' }}">
                        <td class="px-4 py-2 font-bold text-indigo-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">{{ $entry->user->name ?? 'Invitado' }}</td>
                        <td class="px-4 py-2 text-green-400">{{ $entry->score }}</td>
                        <td class="px-4 py-2 text-sm text-gray-400">{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
