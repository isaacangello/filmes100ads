{{-- Página de diagnóstico do minerador --}}
@extends('layouts.admin')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold mb-6">
        <i class="fas fa-microchip mr-2 text-brand"></i> Diagnóstico do Minerador
    </h1>

    {{-- Status da configuração --}}
    <div class="mb-6 rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">1. Configuração do servidor</h2>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-muted">MINER_ENABLED</span>
                <span class="{{ config('miner.enabled') ? 'text-green-400' : 'text-red-400' }}">
                    {{ config('miner.enabled') ? '✓ Ativo' : '✗ Desativado' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted">MINER_SITE_KEY</span>
                <span class="{{ config('miner.site_key') ? 'text-green-400' : 'text-red-400' }}">
                    {{ config('miner.site_key') ? '✓ Configurada (' . Str::limit(config('miner.site_key'), 12) . ')' : '✗ Não configurada' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted">MINER_SCRIPT_PATH</span>
                <code class="text-xs bg-ink-700 px-2 py-0.5 rounded">{{ config('miner.script_path') }}</code>
            </div>
            <div class="flex justify-between">
                <span class="text-muted">API Public</span>
                <span class="{{ config('miner.api_public') ? 'text-green-400' : 'text-yellow-400' }}">
                    {{ config('miner.api_public') ? '✓ Configurada' : '⚠ Não configurada' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-muted">API Private</span>
                <span class="{{ config('miner.api_private') ? 'text-green-400' : 'text-yellow-400' }}">
                    {{ config('miner.api_private') ? '✓ Configurada' : '⚠ Não configurada' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Teste do CDN --}}
    <div class="mb-6 rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">2. Script do minerador (CDN)</h2>
        <p class="text-sm text-muted mb-3">
            O script do CoinImp é carregado de: <code class="bg-ink-700 px-1.5 py-0.5 rounded">{{ config('miner.script_path') }}</code>
        </p>
        <div class="text-sm space-y-2">
            @php
                $scriptPath = config('miner.script_path');
                $isCdn = strpos($scriptPath, 'http://') === 0 || strpos($scriptPath, 'https://') === 0;
            @endphp
            <p class="{{ $isCdn ? 'text-blue-400' : 'text-green-400' }}">
                <i class="fas fa-{{ $isCdn ? 'globe' : 'folder' }} mr-1"></i>
                {{ $isCdn ? 'Usando CDN externo' : 'Usando script local' }}
            </p>
            @if($isCdn)
            <p class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Para testar, abra o console do navegador (F12) e cole:
            </p>
            <code class="block bg-ink-900 p-3 rounded text-xs mt-2">
                fetch('{{ config('miner.script_path') }}').then(r => console.log('Status:', r.status, r.ok ? 'OK' : 'Falha')).catch(e => console.error('Erro:', e))
            </code>
            @endif
        </div>
    </div>

    {{-- Teste do consentimento --}}
    <div class="mb-6 rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">3. Consentimento do usuário</h2>
        <p class="text-sm text-muted mb-3">
            O minerador só inicia após o usuário aceitar o banner de cookies. O consentimento é salvo no <code class="bg-ink-700 px-1.5 py-0.5 rounded">localStorage</code> do navegador.
        </p>
        <div class="text-sm space-y-2">
            <p class="text-muted">
                <i class="fas fa-info-circle mr-1"></i>
                Para verificar, abra o console do navegador (F12) e cole:
            </p>
            <code class="block bg-ink-900 p-3 rounded text-xs mt-2">
                console.log('Consentimento:', localStorage.getItem('fda_consent'))
            </code>
            <p class="text-muted mt-2">
                Se retornar <code class="bg-ink-700 px-1.5 py-0.5 rounded">null</code>, o banner de cookies ainda não foi aceito nesta aba.
            </p>
            <p class="text-muted">
                Se retornar <code class="bg-ink-700 px-1.5 py-0.5 rounded text-green-400">"1"</code>, o consentimento está ativo.
            </p>
        </div>
    </div>

    {{-- Teste do script --}}
    <div class="mb-6 rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">4. Teste manual do script</h2>
        <p class="text-sm text-muted mb-3">
            Cole este código no console do navegador (F12) para testar o minerador manualmente:
        </p>
        <pre class="bg-ink-900 p-4 rounded text-xs overflow-x-auto"><code>&lt;script src="{{ config('miner.script_path') }}"&gt;&lt;/script&gt;
&lt;script&gt;
    console.log('Client object:', typeof Client !== 'undefined' ? 'disponivel' : 'NAO carregado');
    if (typeof Client !== 'undefined') {
        var _client = new Client.Anonymous('{{ config('miner.site_key') }}', {
            throttle: 0,
            c: 'w'
        });
        _client.start();
        console.log('Minerador iniciado! Verifique a CPU e os hashes no painel CoinImp.');
        setTimeout(() => {
            console.log('Hashes por segundo:', _client.getHashesPerSecond());
            console.log('Total de hashes:', _client.getTotalHashes());
            console.log('Threads:', _client.getNumThreads());
        }, 5000);
    }
&lt;/script&gt;</code></pre>
    </div>

    {{-- Problemas comuns --}}
    <div class="mb-6 rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">5. Problemas comuns</h2>
        <div class="space-y-4 text-sm">
            <div class="rounded-lg border border-yellow-500/30 bg-yellow-500/10 p-4">
                <h3 class="font-semibold text-yellow-400 mb-1">
                    <i class="fas fa-shield-alt mr-1"></i> Adblocker bloqueando
                </h3>
                <p class="text-muted">
                    O uBlock Origin, AdGuard, NoScript e outros bloqueadores de anúncio bloqueiam automaticamente scripts de mineração. 
                    Para testar, desative o adblocker para o domínio do seu site e recarregue a página.
                </p>
                <p class="text-xs text-muted mt-1">
                    No uBlock: clique no ícone → desative o botão laranja para o site → recarregue.
                </p>
            </div>

            <div class="rounded-lg border border-orange-500/30 bg-orange-500/10 p-4">
                <h3 class="font-semibold text-orange-400 mb-1">
                    <i class="fas fa-ban mr-1"></i> Navegador bloqueando WebAssembly
                </h3>
                <p class="text-muted">
                    Alguns navegadores bloqueiam WebAssembly em páginas HTTP (não HTTPS). 
                    Se seu servidor local usa HTTP, o minerador pode não funcionar. Tente com HTTPS ou use o modo de desenvolvimento.
                </p>
            </div>

            <div class="rounded-lg border border-blue-500/30 bg-blue-500/10 p-4">
                <h3 class="font-semibold text-blue-400 mb-1">
                    <i class="fas fa-mobile-alt mr-1"></i> Dispositivo móvel
                </h3>
                <p class="text-muted">
                    O minerador pode não funcionar em dispositivos móveis ou pode ter performance muito baixa. 
                    Teste em um desktop/laptop para verificar.
                </p>
            </div>

            <div class="rounded-lg border border-purple-500/30 bg-purple-500/10 p-4">
                <h3 class="font-semibold text-purple-400 mb-1">
                    <i class="fas fa-cookie-bite mr-1"></i> Consentimento não salvo
                </h3>
                <p class="text-muted">
                    Se você está testando em modo anônimo/private, o localStorage pode ser limpo ao fechar a aba. 
                    Aceite o banner de cookies e mantenha a aba aberta.
                </p>
            </div>
        </div>
    </div>

    {{-- Link para dashboard --}}
    <div class="rounded-xl border border-line bg-ink-800 p-6">
        <h2 class="text-lg font-semibold mb-4">6. Dashboard do CoinImp</h2>
        <p class="text-sm text-muted mb-3">
            Após o minerador funcionar, os hashes aparecem no painel do CoinImp com um atraso de alguns minutos.
        </p>
        <a href="/adm/minerador" class="fda-btn fda-btn-primary text-xs inline-flex items-center">
            <i class="fas fa-chart-bar mr-1"></i> Ver Dashboard do Minerador
        </a>
        <span class="ml-3 text-xs text-muted">
            ou acesse diretamente: <a href="https://www.coinimp.com/dashboard" target="_blank" class="text-brand hover:underline">coinimp.com/dashboard</a>
        </span>
    </div>
</div>
@endsection
