@extends('layout')

@section('content')
    <div
        class="min-h-screen bg-gradient-to-tr from-slate-800 via-slate-900 to-black text-white px-4 pt-20 pb-10 flex flex-col items-center">

        <h2 class="text-4xl font-extrabold mb-10 text-center">Elige tu juego</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 w-full max-w-3xl">

            <a href="{{ route('wordle.index') }}"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 rounded-xl shadow-lg hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <span class="text-2xl font-bold mb-2">Reto Diario</span>
                <span class="text-sm text-zinc-200">Adivina la palabra del día</span>
            </a>

            <a href="{{ route('battleship.index') }}"
                class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 rounded-xl shadow-lg hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <span class="text-2xl font-bold mb-2">Hundir la flota</span>
                <span class="text-sm text-zinc-200">Conviertete en el cápitan en esta batalla naval</span>
            </a>

            <a href="{{ route('speedclick.index') }}"
                class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 rounded-xl shadow-lg hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <span class="text-2xl font-bold mb-2">Speed Click: Velocidad</span>
                <span class="text-sm text-zinc-200">Pon a prueba tus reflejos</span>
            </a>

            <a href="{{ route('battleship.index') }}"
                class="bg-gradient-to-r from-blue-600 to-sky-500 p-6 rounded-xl shadow-lg hover:scale-105 transition-transform duration-300 flex flex-col items-center text-center">
                <span class="text-2xl font-bold mb-2">Hundir la flota</span>
                <span class="text-sm text-zinc-200">Clásico vs IA y PVP</span>
            </a>


        </div>
    </div>
@endsection