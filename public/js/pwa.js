(function () {
    'use strict';

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
        });
    }

    var deferredPrompt = null;

    function isStandalone() {
        return window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;
    }

    function show(selector) {
        document.querySelectorAll(selector).forEach(function (el) {
            el.classList.remove('d-none');
            el.removeAttribute('hidden');
        });
    }

    function hide(selector) {
        document.querySelectorAll(selector).forEach(function (el) {
            el.classList.add('d-none');
        });
    }

    function installHint() {
        var text = 'Para instalar el POS en este equipo:\n\n'
            + '• Brave o Chrome: menú del navegador (⋮) → “Instalar…” o “Crear acceso directo…”\n'
            + '• En la PC de caja con HTTP local puede no aparecer “Instalar”; igual podés crear un acceso directo.\n'
            + '• En el celular: menú → “Agregar a pantalla de inicio”.';

        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Instalar POS',
                text: text,
                icon: 'info',
                confirmButtonText: 'Entendido',
            });

            return;
        }

        window.alert(text);
    }

    function syncInstallUi() {
        if (isStandalone()) {
            hide('[data-pwa-install]');

            return;
        }

        show('[data-pwa-install]');
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        syncInstallUi();
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-pwa-install]');
        if (!trigger) {
            return;
        }

        event.preventDefault();

        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.finally(function () {
                deferredPrompt = null;
                syncInstallUi();
            });

            return;
        }

        installHint();
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        hide('[data-pwa-install]');
    });

    window.addEventListener('load', syncInstallUi);

    var ios = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

    if (ios && !isStandalone()) {
        show('[data-pwa-ios-hint]');
    }
})();
