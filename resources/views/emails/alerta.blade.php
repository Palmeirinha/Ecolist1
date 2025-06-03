{{-- filepath: resources/views/emails/alerta.blade.php --}}
<p>Olá, {{ $alimento->user->name }}!</p>
<p>O alimento <strong>{{ $alimento->nome }}</strong> está próximo do vencimento (validade: {{ $alimento->validade }}).</p>
<p>Não se esqueça de utilizá-lo!</p>