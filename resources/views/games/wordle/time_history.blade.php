@extends('layout')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-zinc-900 to-black text-white px-6 py-10">
    <h2 class="text-4xl font-extrabold text-purple-400 mb-6 text-center">Historial de Wordle Contrarreloj</h2>

    <div class="max-w-2xl mx-auto bg-zinc-800 rounded-lg shadow p-6">
        @if($puntuaciones->isEmpty())
            <p class="text-center text-zinc-400">Todavía no has jugado ninguna partida.</p>
        @else
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-zinc-400 border-b border-zinc-600">
                        <th class="py-2">#</th>
                        <th class="py-2">Puntuación</th>
                        <th class="py-2">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($puntuaciones as $index => $p)
                        <tr class="border-b border-zinc-700 hover:bg-zinc-700">
                            <td class="py-2">{{ $index + 1 }}</td>
                            <td class="py-2 text-green-400 font-bold">{{ $p->score }}</td>
                            <td class="py-2 text-sm text-zinc-300">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="text-center mt-6">
        <a href="{{ route('games.index') }}" class="inline-block bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
            Volver a los juegos
        </a>
    </div>
</div>
@endsection
