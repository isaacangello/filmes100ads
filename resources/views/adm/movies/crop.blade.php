@extends('layouts.admin')

@section('page-title', 'Recortar capa')

@section('content')
<div class="mx-auto max-w-4xl space-y-5">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="font-semibold">Recortar capa do filme</h2>
            <p class="mt-1 text-sm text-muted">
                Arraste ou redimensione o retângulo para escolher a área que será usada como pôster (proporção 2:3).
            </p>
        </div>

        <a href="{{ route('cadastro.index') }}" class="fda-btn fda-btn-ghost">
            <i class="fas fa-arrow-left text-xs"></i> Voltar
        </a>
    </div>

    <div class="rounded-2xl border border-line bg-ink-800 p-5 shadow-card">
        {{-- Elemento onde o Croppie é montado --}}
        <div id="croppie" class="mx-auto max-w-[360px]"></div>

        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
            <button type="button" id="zoom-in" class="fda-btn fda-btn-ghost" title="Aproximar">
                <i class="fas fa-search-plus text-xs"></i> Aproximar
            </button>
            <button type="button" id="zoom-out" class="fda-btn fda-btn-ghost" title="Afastar">
                <i class="fas fa-search-minus text-xs"></i> Afastar
            </button>
            <button type="button" id="rotate" class="fda-btn fda-btn-ghost" title="Girar 90°">
                <i class="fas fa-rotate-right text-xs"></i> Girar
            </button>
        </div>
    </div>

    <form id="crop-form" action="{{ route('cadastro.salvando') }}" method="POST" class="space-y-4">
        @csrf
        <input type="hidden" name="crop" value="1">
        <input type="hidden" id="x" name="x">
        <input type="hidden" id="y" name="y">
        <input type="hidden" id="w" name="w">
        <input type="hidden" id="h" name="h">

        <div id="crop-error" class="hidden rounded-lg border border-red-500/40 bg-red-500/12 px-4 py-3 text-sm text-red-300">
            Selecione a região para recortar a imagem.
        </div>

        <div class="flex items-center justify-end gap-3 pb-6">
            <a href="{{ route('cadastro.index') }}" class="fda-btn fda-btn-ghost">Cancelar</a>
            <button type="submit" id="crop-submit" class="fda-btn fda-btn-primary">
                <i class="fas fa-crop-simple text-xs"></i> Recortar e salvar
            </button>
        </div>
    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('croppie');

        if (!el || typeof window.Croppie === 'undefined') {
            document.getElementById('crop-error').classList.remove('hidden');
            document.getElementById('crop-error').textContent = 'Não foi possível carregar o recorte de imagem.';
            return;
        }

        var croppie = new window.Croppie(el, {
            aspectRatio: 11 / 16,
            viewMode: 1,
            boundary: { width: 360, height: 560 },
            autoReplace: true,
            autoOrientation: true,
            responsive: true,
            restore: false,
        });

        croppie.bind({
            url: @json(asset('storage/temp/tempimage'.Auth::user()->id.'.jpg')),
        });

        var form = document.getElementById('crop-form');
        var errorBox = document.getElementById('crop-error');

        document.getElementById('zoom-in').addEventListener('click', function () {
            croppie.setZoom(croppie.get().zoom + 0.1);
        });

        document.getElementById('zoom-out').addEventListener('click', function () {
            croppie.setZoom(croppie.get().zoom - 0.1);
        });

        document.getElementById('rotate').addEventListener('click', function () {
            croppie.rotate(90);
        });

        form.addEventListener('submit', function (event) {
            var points = croppie.get().points;
            var width = Math.round(points[2] - points[0]);
            var height = Math.round(points[3] - points[1]);

            if (!width || !height) {
                event.preventDefault();
                errorBox.classList.remove('hidden');
                return;
            }

            errorBox.classList.add('hidden');
            document.getElementById('x').value = Math.round(points[0]);
            document.getElementById('y').value = Math.round(points[1]);
            document.getElementById('w').value = width;
            document.getElementById('h').value = height;
        });
    });
</script>
@endsection
