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

    // ── Prévisualisation d'image avant upload ──────────────
    document.querySelectorAll('input[type="file"][accept^="image"]').forEach(function (input) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;

            const existingPreview = this.parentElement.querySelector('.js-img-preview');
            if (existingPreview) existingPreview.remove();

            const img = document.createElement('img');
            img.className  = 'img-thumbnail mt-2 js-img-preview';
            img.style.height = '80px';
            img.src        = URL.createObjectURL(file);
            img.onload     = () => URL.revokeObjectURL(img.src);
            this.parentElement.appendChild(img);
        });
    });

});
