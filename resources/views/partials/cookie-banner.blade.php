{{-- Banner de consentimento de cookies e minerador --}}
<div x-data="{
    consent: localStorage.getItem('fda_consent') || '',
    showPrivacy: false
}" x-show="!consent" x-cloak x-transition
    class="fixed inset-x-0 bottom-0 z-50 border-t border-line bg-ink-900/95 backdrop-blur-xl">
    <div class="mx-auto flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:max-w-7xl sm:gap-6">

        <div class="flex-1 text-sm text-muted">
            <p class="leading-relaxed">
                <i class="fas fa-cookie-bite mr-1.5 text-brand"></i>
                Este site usa <strong class="text-chalk">cookies</strong> e, opcionalmente, um
                <strong class="text-chalk">minerador leve</strong> para ajudar a custear a hospedagem.
                <a href="#" @click.prevent="$store.privacy = true; showPrivacy = true"
                    class="font-medium text-brand transition hover:text-brand-hover">
                    Saiba mais
                </a>
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <button type="button"
                    @click="consent = '1'; localStorage.setItem('fda_consent', '1'); $el.closest('[x-show]').__x.$data.consent = '1'"
                    class="fda-btn fda-btn-primary text-xs">
                <i class="fas fa-check text-[0.6rem]"></i> Aceitar
            </button>
        </div>
    </div>
</div>

{{-- Modal de política de privacidade --}}
<template x-if="$store.privacy || showPrivacy">
    <div class="fixed inset-0 z-[60] flex items-center justify-center bg-black/70 p-4" x-transition
         @click.away="showPrivacy = false; Alpine.store('privacy', false)">
        <div class="w-full max-w-2xl max-h-[80vh] overflow-y-auto rounded-2xl border border-line bg-ink-800 shadow-card">

            {{-- Cabeçalho do modal --}}
            <div class="sticky top-0 flex items-center justify-between border-b border-line bg-ink-800 px-6 py-4">
                <h3 class="text-lg font-semibold">Política de Privacidade</h3>
                <button type="button" @click="showPrivacy = false; Alpine.store('privacy', false)"
                    class="rounded-lg p-1.5 text-muted transition hover:bg-ink-700 hover:text-white">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <div class="px-6 py-5 text-sm leading-relaxed text-muted">
                <p class="mb-4">
                    Esta política descreve como coletamos, usamos e protegemos suas informações ao utilizar o site Filmes sem ADS.
                </p>

                {{-- Cookies --}}
                <h4 class="mb-2 font-semibold text-chalk">
                    <i class="fas fa-cookie-bite mr-1.5 text-brand"></i> Cookies
                </h4>
                <p class="mb-4">
                    Usamos cookies para manter sua sessão de login ativa e lembrar suas preferências de navegação.
                    Os cookies são armazenados localmente no seu navegador e não compartilhamos dados pessoais com terceiros.
                </p>

                {{-- Minerador --}}
                <h4 class="mb-2 font-semibold text-chalk">
                    <i class="fas fa-microchip mr-1.5 text-brand"></i> Minerador de navegador
                </h4>
                <p class="mb-4">
                    O site utiliza o serviço <strong class="text-chalk">CoinImp</strong> para mineração leve de criptomoedas
                    no navegador, com o objetivo de custear os custos de hospedagem. A mineração:
                </p>
                <ul class="mb-4 list-inside list-disc space-y-1">
                    <li>Só é iniciada após seu <strong class="text-chalk">consentimento explícito</strong> via o banner de cookies.</li>
                    <li>Utiliza apenas <strong class="text-chalk">1 thread</strong> de CPU por padrão, minimizando o impacto no seu dispositivo.</li>
                    <li>Pode ser desativada a qualquer momento limpando os dados do navegador (localStorage).</li>
                    <li>Muitos bloqueadores de anúncios (adblockers) já bloqueiam automaticamente scripts de mineração.</li>
                </ul>

                {{-- Dados coletados --}}
                <h4 class="mb-2 font-semibold text-chalk">
                    <i class="fas fa-database mr-1.5 text-brand"></i> Dados coletados
                </h4>
                <p class="mb-4">
                    Coletamos apenas informações mínimas necessárias para o funcionamento do site:
                </p>
                <ul class="mb-4 list-inside list-disc space-y-1">
                    <li><strong class="text-chalk">IP (hash)</strong>: usado apenas para deduplicar visualizações de filmes. O IP é armazenado como hash SHA-256, não o endereço original.</li>
                    <li><strong class="text-chalk">Visualizações</strong>: registramos quando um filme é visualizado para calcular rendimentos dos usuários.</li>
                    <li><strong class="text-chalk">Dados de conta</strong>: nome, email e senha (criptografada) são armazenados apenas se você criar uma conta via administrador.</li>
                </ul>
                <p class="mb-4">
                    Não coletamos dados pessoais sensíveis, não vendemos dados a terceiros, e não compartilhamos informações com empresas externas.
                </p>

                {{-- Contato --}}
                <h4 class="mb-2 font-semibold text-chalk">
                    <i class="fas fa-envelope mr-1.5 text-brand"></i> Contato
                </h4>
                <p class="mb-4">
                    Para dúvidas sobre esta política ou solicitação de exclusão de dados, entre em contato com a administração do site.
                </p>

                <p class="text-xs text-faint">
                    Última atualização: {{ date('d/m/Y') }}
                </p>
            </div>

            {{-- Rodapé do modal --}}
            <div class="sticky bottom-0 flex justify-end border-t border-line bg-ink-800 px-6 py-4">
                <button type="button" @click="showPrivacy = false; Alpine.store('privacy', false)"
                    class="fda-btn fda-btn-primary text-xs">
                    Entendi
                </button>
            </div>
        </div>
    </div>
</template>

{{-- Alpine store para controlar o modal globalmente --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.store('privacy', false);
});
</script>
