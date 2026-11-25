{{-- Consentimento de cookies e aviso de mineração são independentes. --}}
<div x-show="!cookieConsent && !(minerConsentRequired && minerConsent === '')" x-cloak x-transition
    :inert="minerConsentRequired && minerConsent === ''"
    class="fixed inset-x-0 bottom-0 z-50 border-t border-line bg-ink-900/95 backdrop-blur-xl">
    <div class="mx-auto flex flex-col gap-4 px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:max-w-7xl sm:gap-6">
        <div class="flex-1 text-sm text-muted">
            <p class="leading-relaxed">
                <i class="fas fa-cookie-bite mr-1.5 text-brand"></i>
                Este site usa cookies necessários para seu funcionamento. A escolha sobre mineração é separada.
                <a href="#" @click.prevent="$store.privacy = true; showPrivacy = true"
                    class="font-medium text-brand transition hover:text-brand-hover">
                    Saiba mais
                </a>
            </p>
        </div>

        <div class="flex shrink-0 items-center gap-3">
            <button type="button"
                @click="cookieConsent = '1'; localStorage.setItem('fda_consent', '1')"
                class="fda-btn fda-btn-primary text-xs">
                <i class="fas fa-check text-[0.6rem]"></i> Aceitar cookies
            </button>
        </div>
    </div>
</div>

@if ($isMinerHome)
    <section x-show="minerConfigured && minerConsent === ''" x-cloak x-transition
        x-init="if (minerConfigured && minerConsent === '') $nextTick(() => $refs.minerAccept.focus())"
        role="dialog" aria-modal="true" aria-labelledby="miner-consent-title"
        aria-describedby="miner-consent-description"
        data-miner-consent-panel
        data-miner-configured="{{ $minerConfigured ? 'true' : 'false' }}"
        data-miner-site-key="{{ $minerConfigured ? config('miner.site_key') : '' }}"
        data-miner-script-src="{{ $minerScriptSrc }}"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/80 p-4">
        <div class="w-full max-w-lg rounded-2xl border border-line bg-ink-900 p-6 shadow-card sm:p-8">
            <h2 id="miner-consent-title" class="mb-3 text-xl font-bold text-chalk">
                <i class="fas fa-microchip mr-1.5 text-brand"></i> Antes de entrar
            </h2>
            <div id="miner-consent-description" class="space-y-3 text-sm leading-relaxed text-muted">
                <p>
                    O acesso à página inicial requer autorização para executar mineração de criptomoedas usando parte da CPU deste dispositivo. Isso ajuda a custear a hospedagem e pode aumentar o consumo de energia/bateria e a temperatura do aparelho.
                </p>
                <p>
                    Ao aceitar e entrar, sua autorização ficará salva neste navegador e a mineração será iniciada nas próximas visitas se o serviço estiver disponível. Você poderá pará-la a qualquer momento pelo botão redondo no rodapé; depois de parar, ofereceremos a opção de reativá-la quando você voltar.
                </p>
                <p>
                    Esta autorização é independente dos cookies e não será inferida do consentimento de cookies já salvo.
                </p>
            </div>

            <div class="mt-6 flex justify-end">
                <button x-ref="minerAccept" type="button" @click="setMinerConsent('1')"
                    class="fda-btn fda-btn-primary">
                    <i class="fas fa-check text-xs"></i> Aceitar e entrar
                </button>
            </div>
        </div>
    </section>
@endif

@if ($isMinerPublic)
    <section x-show="showMinerRestartPrompt" x-cloak x-transition
        data-miner-restart-prompt role="region" aria-labelledby="miner-restart-title"
        class="fixed inset-x-4 bottom-4 z-[70] mx-auto max-w-lg rounded-2xl border border-line bg-ink-900 p-5 shadow-card">
        <h2 id="miner-restart-title" class="mb-2 font-semibold text-chalk">
            <i class="fas fa-microchip mr-1.5 text-brand"></i> Mineração parada
        </h2>
        <p class="text-sm leading-relaxed text-muted">
            Reativar ajuda a custear a hospedagem do projeto. Você também pode continuar navegando sem mineração; sua pausa será respeitada e o convite poderá aparecer novamente quando voltar ao site.
        </p>
        <div class="mt-4 flex flex-wrap justify-end gap-3">
            <button type="button" @click="showMinerRestartPrompt = false"
                class="fda-btn fda-btn-ghost text-xs">
                Continuar sem mineração
            </button>
            <button type="button" @click="setMinerConsent('1')"
                class="fda-btn fda-btn-primary text-xs">
                <i class="fas fa-play text-[0.6rem]"></i> Ativar novamente
            </button>
        </div>
    </section>
@endif

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
                    Quando habilitada pelo site e autorizada por você, a integração com o serviço
                    <strong class="text-chalk">CoinImp</strong> pode usar recursos do navegador para mineração de criptomoedas,
                    ajudando a custear a hospedagem. A mineração:
                </p>
                <ul class="mb-4 list-inside list-disc space-y-1">
                    <li>Só é iniciada após sua <strong class="text-chalk">autorização específica</strong> no aviso de mineração, independente dos cookies.</li>
                    <li>Pode ser interrompida a qualquer momento pelo botão “Para mineração” no rodapé das páginas públicas.</li>
                    <li>Muitos bloqueadores de anúncios (adblockers) já bloqueiam automaticamente scripts de mineração.</li>
                </ul>
                <p class="mb-4">
                    Sua autorização fica salva neste navegador. Se você parar a mineração, ela permanecerá desligada até decidir reativá-la; ao voltar a uma página pública, o convite poderá ser mostrado novamente.
                </p>

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
