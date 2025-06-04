<x-app-layout>
    <div class="max-w-4xl mx-auto p-4">
        <form method="GET" action="{{ url('/receitas/sugestoes') }}" class="mb-4">
            <input type="text" name="ingrediente" placeholder="Digite um ingrediente"
                class="border p-2 rounded w-1/2" />
            <button type="submit"
                class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Buscar</button>
        </form>

        <h2 class="text-2xl font-bold mb-4">Receitas com: {{ $ingrediente }}</h2>

        @if (count($receitas) > 0)
            <ul class="space-y-2">
                @foreach ($receitas as $receita)
                    <li class="bg-white shadow rounded p-3">{{ $receita['strMeal'] }}</li>
                @endforeach
            </ul>
        @else
            <p class="text-red-600">Nenhuma receita encontrada.</p>
        @endif
    </div>
</x-app-layout>
