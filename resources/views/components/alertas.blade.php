@props(['alertas'])

<div class="space-y-4">
    @forelse($alertas as $alerta)
        <div x-data="{ show: true }"
             x-show="show"
             class="relative bg-white p-4 rounded-lg shadow-md border-l-4 {{ $alerta->tipo === 'vencimento' ? 'border-yellow-500' : 'border-blue-500' }}">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="text-gray-800">{{ $alerta->mensagem }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $alerta->created_at->diffForHumans() }}
                    </p>
                </div>
                
                <div class="flex items-center space-x-2">
                    @if(!$alerta->lido)
                        <form action="{{ route('alertas.marcar-como-lido', $alerta) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" 
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                Marcar como lido
                            </button>
                        </form>
                    @endif
                    
                    <button @click="show = false"
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" 
                                  stroke-linejoin="round" 
                                  stroke-width="2" 
                                  d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    @empty
        <div class="text-center py-4 text-gray-500">
            Nenhum alerta no momento.
        </div>
    @endforelse
</div> 