{{-- filepath: resources/views/alimentos/receitas.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Receitas sugeridas com seus alimentos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            @if(empty($receitas))
                <p>Nenhuma receita encontrada para seus alimentos.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($receitas as $receita)
                        <div class="bg-white p-4 rounded shadow">
                            <h3 class="font-bold text-lg mb-2">{{ $receita['strMeal'] ?? 'Receita' }}</h3>
                            @if(isset($receita['strMealThumb']))
                                <img src="{{ $receita['strMealThumb'] }}" alt="{{ $receita['strMeal'] }}" class="mb-2 w-full h-48 object-cover rounded">
                            @endif
                            <a href="{{ $receita['strSource'] ?? '#' }}" target="_blank" class="text-blue-600 underline">Ver receita</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>