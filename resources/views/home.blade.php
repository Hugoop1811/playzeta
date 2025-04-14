@extends('layout')

@section('content')
    <div class="text-center">
        <h2 class="text-4xl font-semibold mb-4">Bienvenido a PlayZeta</h2>
        <p class="mb-6 text-gray-400">Elige cómo quieres empezar</p>

        <div class="space-x-4">
            <button class="bg-indigo-600 hover:bg-indigo-500 px-6 py-2 rounded-lg font-medium">
                Jugar como invitado
            </button>
            <button class="bg-gray-700 hover:bg-gray-600 px-6 py-2 rounded-lg font-medium">
                Acceder
            </button>
        </div>
    </div>
@endsection
