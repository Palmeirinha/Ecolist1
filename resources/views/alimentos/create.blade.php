{{-- filepath: resources/views/alimentos/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cadastrar Alimento
        </h2>
    </x-slot>

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 px-4">
        <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
            <a href="{{ route('alimentos.index') }}"
               class="inline-block mb-4 px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 transition-colors duration-200">
                ← Voltar
            </a>
            <form action="{{ route('alimentos.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <x-input-group name="nome" label="Nome do alimento" required />
                </div>
                <div class="mb-4">
                    <label for="tipo_quantidade" class="block text-gray-700">Tipo de quantidade</label>
                    <select name="tipo_quantidade" id="tipo_quantidade" class="w-full border rounded p-2" onchange="atualizaLimite()">
                        <option value="unidade" {{ old('tipo_quantidade', $alimento->tipo_quantidade ?? '') == 'unidade' ? 'selected' : '' }}>Unidade</option>
                        <option value="quilo" {{ old('tipo_quantidade', $alimento->tipo_quantidade ?? '') == 'quilo' ? 'selected' : '' }}>Quilo</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="quantidade" class="block text-gray-700">Quantidade</label>
                    <input type="number" name="quantidade" id="quantidade" class="w-full border rounded p-2"
                        value="{{ old('quantidade', $alimento->quantidade ?? '') }}"
                        min="1" max="{{ old('tipo_quantidade', $alimento->tipo_quantidade ?? '') == 'quilo' ? 10 : 100 }}">
                @error('quantidade')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                </div>
                <div class="mb-4">
                    <label for="validade" class="block text-gray-700">Validade</label>
                    <input type="date" name="validade" id="validade" class="w-full border rounded p-2" value="{{ old('validade') }}">
                    @error('validade')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="categoria_id" class="block text-gray-700">Categoria</label>
                    <select name="categoria_id" id="categoria_id" class="w-full border rounded p-2">
                        <option value="">Selecione</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nome }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-200 shadow mt-4">
                    Salvar
                </button>
            </form>
            <script>
                document.querySelector('form').addEventListener('submit', function(e) {
                    document.getElementById('btnSalvar').disabled = true;
                    document.getElementById('btnSalvar').innerText = 'Salvando...';
                });

                function atualizaLimite() {
                    const tipo = document.getElementById('tipo_quantidade').value;
                    const quantidade = document.getElementById('quantidade');
                    if (tipo === 'quilo') {
                        quantidade.max = 10;
                    } else {
                        quantidade.max = 100;
                    }
                }
                document.addEventListener('DOMContentLoaded', atualizaLimite);
            </script>
        </div>
    </div>
</x-app-layout>
