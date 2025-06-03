<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name', 'EcoList') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss/dist/tailwind.min.css">
</head>
<body>
    <header class="bg-gray-800 text-white p-4 flex justify-between items-center">
        <div>
            <a href="{{ route('dashboard') }}" class="mr-4 hover:underline">Dashboard</a>
            <a href="{{ route('alimentos.index') }}" class="mr-4 hover:underline">Alimentos</a>
            <a href="{{ route('alimentos.create') }}" class="mr-4 hover:underline">Novo Alimento</a>
            <!-- Botão de logout -->
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="ml-4 text-red-400 hover:underline bg-transparent border-none cursor-pointer">
                    Sair
                </button>
            </form>
            <!-- Botão para alternar cor -->
            <button onclick="toggleTheme()" id="themeBtn" class="ml-4 px-2 py-1 rounded bg-gray-200 text-gray-800">Modo escuro</button>
        </div>
    </header>
    <main>
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <script>
    function toggleTheme() {
        const body = document.body;
        const btn = document.getElementById('themeBtn');
        if (body.classList.contains('bg-black')) {
            body.classList.remove('bg-black', 'text-white');
            body.classList.add('bg-white', 'text-gray-900');
            btn.textContent = 'Modo escuro';
        } else {
            body.classList.remove('bg-white', 'text-gray-900');
            body.classList.add('bg-black', 'text-white');
            btn.textContent = 'Modo claro';
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        document.body.classList.add('bg-white', 'text-gray-900');
    });
    </script>
</body>
</html>
