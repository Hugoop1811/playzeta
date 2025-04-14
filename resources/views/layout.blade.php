<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>PlayZeta</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-900 text-white min-h-screen flex flex-col">
    <header class="bg-gray-800 p-4 text-center shadow-md">
        <h1 class="text-3xl font-bold text-indigo-400">PlayZeta</h1>
        <p class="text-sm text-gray-400">Juega. Compite. Disfruta.</p>
    </header>

    <main class="flex-grow container mx-auto px-4 py-8">
        @yield('content')
    </main>

    <footer class="bg-gray-800 p-4 text-center text-sm text-gray-500">
        &copy; 2025 PlayZeta. Todos los derechos reservados.
    </footer>
</body>
</html>
