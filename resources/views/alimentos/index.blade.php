<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Lista de Alimentos
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form method="GET" action="{{ route('alimentos.index') }}" class="mb-4">
                <label for="categoria_id" class="mr-2">Filtrar por categoria:</label>
                <select name="categoria_id" id="categoria_id" class="border rounded p-1">
                    <option value="">Todas</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                            {{ $categoria->nome }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="ml-2 px-3 py-1 bg-blue-500 text-white rounded">Filtrar</button>
            </form>

            <a href="{{ route('alimentos.create') }}" class="mb-4 inline-block px-4 py-2 bg-green-500 text-white rounded">Novo Alimento</a>

            @if($alimentos->isEmpty())
                <p>Nenhum alimento encontrado.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white rounded shadow">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 bg-gray-100">Nome</th>
                                <th class="py-2 px-4 bg-gray-100">Quantidade</th>
                                <th class="py-2 px-4 bg-gray-100">Validade</th>
                                <th class="py-2 px-4 bg-gray-100">Categoria</th>
                                <th class="py-2 px-4 bg-gray-100">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alimentos as $alimento)
                                <tr>
                                    <td class="py-2 px-4">{{ $alimento->nome }}</td>
                                    <td class="py-2 px-4">{{ $alimento->quantidade }}</td>
                                    <td class="py-2 px-4">{{ $alimento->validade }}</td>
                                    <td class="py-2 px-4">{{ $alimento->categoria ? $alimento->categoria->nome : '-' }}</td>
                                    <td class="py-2 px-4">
                                        <a href="{{ route('alimentos.edit', $alimento) }}" class="text-blue-600 underline">Editar</a>
                                        <form action="{{ route('alimentos.destroy', $alimento) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 underline" onclick="return confirm('Tem certeza?')">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
