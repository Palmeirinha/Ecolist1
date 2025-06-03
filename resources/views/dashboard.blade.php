<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="bg-green-100 p-4 rounded shadow">
                    <p class="text-lg font-bold">Total de alimentos</p>
                    <p class="text-2xl">{{ $total }}</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded shadow">
                    <p class="text-lg font-bold">Vencendo em breve</p>
                    <p class="text-2xl">{{ $vencendo }}</p>
                </div>
                <div class="bg-red-100 p-4 rounded shadow">
                    <p class="text-lg font-bold">Vencidos</p>
                    <p class="text-2xl">{{ $vencidos }}</p>
                </div>
            </div>
            <canvas id="grafico"></canvas>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('grafico');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Total', 'Vencendo', 'Vencidos'],
                datasets: [{
                    label: 'Alimentos',
                    data: [{{ $total }}, {{ $vencendo }}, {{ $vencidos }}],
                    backgroundColor: ['#4ade80', '#facc15', '#f87171']
                }]
            }
        });
    </script>
</x-app-layout>
