<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PlayZeta</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col">

    <header class="bg-gray-800 p-4 text-center shadow-md">
        <a href="{{ route('games.index') }}" class="text-2xl font-bold text-purple-400 hover:text-purple-300 transition-colors">
    PlayZeta
</a>

        <p class="text-sm text-gray-400">Juega. Compite. Disfruta.</p>
        <div class="absolute right-4 top-4">
    @auth
        <span class="text-sm text-white mr-2">Hola, {{ Auth::user()->name }}</span>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="text-sm text-red-400 hover:text-red-600">Cerrar sesión</button>
        </form>
    @else
        <a href="{{ route('login') }}" class="text-sm text-blue-400 hover:text-blue-600">Iniciar sesión</a>
    @endauth
</div>

    </header>

    <main class="flex-grow container mx-auto px-4 py-8">

        @yield('content')
    </main>

    <footer class="bg-gray-800 p-4 text-center text-sm text-gray-500">
        &copy; 2025 PlayZeta. Todos los derechos reservados.
    </footer>
@stack('scripts')
</body>
</html>
