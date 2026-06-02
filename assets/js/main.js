/* ============================================================
   DriveLoc – main.js
   Comportements globaux côté client.
   ============================================================ */

'use strict';

document.addEventListener('DOMContentLoaded', function () {

    // ── Auto-dismiss des alertes flash (5 s) ──────────────
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });

    // ── Confirmation de suppression via data-confirm ───────
    // Usage : <button data-confirm="Êtes-vous sûr ?">Supprimer</button>
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!window.confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // ── Prévisualisation d'image depuis URL ─────────────────
    document.querySelectorAll('input[type="url"][name="main_image"]').forEach(function (input) {
        const showPreview = function () {
            const value = input.value.trim();
            const existingPreview = input.parentElement.querySelector('.js-img-preview');
            if (existingPreview) existingPreview.remove();

            if (!value || (!value.startsWith('http://') && !value.startsWith('https://'))) {
                return;
            }

            const img = document.createElement('img');
            img.className = 'img-thumbnail mt-2 js-img-preview';
            img.style.height = '80px';
            img.src = value;
            img.alt = 'Prévisualisation image';
            img.onerror = function () { img.remove(); };
            input.parentElement.appendChild(img);
        };

        input.addEventListener('input', showPreview);
        showPreview();
    });

});
