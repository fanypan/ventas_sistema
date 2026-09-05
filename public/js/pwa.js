(function () {
    'use strict';

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(function () {});
        });
    }

    var deferredPrompt = null;

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

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        show('[data-pwa-install]');
    });

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest('[data-pwa-install]');
        if (!trigger || !deferredPrompt) {
            return;
        }

        event.preventDefault();
        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () {
            deferredPrompt = null;
            hide('[data-pwa-install]');
        });
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        hide('[data-pwa-install]');
    });

    var standalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    var ios = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

    if (ios && !standalone) {
        show('[data-pwa-ios-hint]');
    }
})();
