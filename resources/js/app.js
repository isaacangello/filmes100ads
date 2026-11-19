/*
 |--------------------------------------------------------------------------
 | Scripts do catálogo público
 |--------------------------------------------------------------------------
 |
 | O Alpine.js vem junto com o Livewire 3 (carregado via @livewireScripts).
 | Aqui ficam o Croppie (crop de capa) e os helpers globais do site.
 |
 */

import Croppie from 'croppie';
import 'croppie/croppie.css';

window.Croppie = Croppie;

window.FDA = {
    /**
     * Formata bytes em texto legível (ex.: 1.4 GB).
     */
    formatBytes(bytes, decimals = 1) {
        if (!bytes || bytes <= 0) return '0 B';

        const units = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

        return `${(bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : decimals)} ${units[i]}`;
    },
};
