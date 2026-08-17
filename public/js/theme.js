(function () {
    'use strict';

    var STORAGE_KEY = 'theme';
    var PREF_KEY = 'theme-preference';

    var labels = {
        system: 'Tema del sistema',
        light: 'Tema claro',
        dark: 'Tema oscuro',
    };

    var icons = {
        system: 'fas fa-desktop',
        light: 'fas fa-sun',
        dark: 'fas fa-moon',
    };

    function systemPrefersDark() {
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    function normalizePreference(raw) {
        if (raw === 'dark' || raw === 'dark-mode') return 'dark';
        if (raw === 'light') return 'light';
        if (raw === 'system') return 'system';
        return 'system';
    }

    function getPreference() {
        try {
            var stored = localStorage.getItem(PREF_KEY) || localStorage.getItem(STORAGE_KEY);
            return normalizePreference(stored);
        } catch (e) {
            return 'system';
        }
    }

    function resolvedMode(preference) {
        if (preference === 'dark') return 'dark';
        if (preference === 'light') return 'light';
        return systemPrefersDark() ? 'dark' : 'light';
    }

    function applyTheme(preference) {
        var mode = resolvedMode(preference);
        var root = document.documentElement;
        var body = document.body;

        root.classList.toggle('dark-mode', mode === 'dark');
        root.setAttribute('data-theme-preference', preference);
        root.style.colorScheme = mode;

        if (body) {
            body.classList.toggle('dark-mode', mode === 'dark');
            body.classList.toggle('light', mode === 'light');
        }

        try {
            localStorage.setItem(PREF_KEY, preference);
            localStorage.setItem(STORAGE_KEY, mode === 'dark' ? 'dark-mode' : 'light');
        } catch (e) {}

        updateThemeMenu(preference, mode);
    }

    function updateThemeMenu(preference, mode) {
        var toggle = document.getElementById('btntheme');
        var icon = document.getElementById('icontheme');

        if (icon) {
            icon.className = icons[preference] || icons.system;
        }

        if (toggle) {
            toggle.setAttribute('title', labels[preference] || labels.system);
            toggle.setAttribute('aria-label', 'Elegir tema. Actual: ' + (labels[preference] || labels.system));
            toggle.setAttribute('data-theme-preference', preference);
            toggle.setAttribute('data-theme-mode', mode);
        }

        document.querySelectorAll('.theme-option').forEach(function (option) {
            var active = option.getAttribute('data-theme-preference') === preference;
            option.classList.toggle('active', active);
            option.setAttribute('aria-checked', active ? 'true' : 'false');
        });
    }

    window.applyAppTheme = applyTheme;
    window.getAppThemePreference = getPreference;

    applyTheme(getPreference());

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getPreference());

        document.querySelectorAll('.theme-option').forEach(function (option) {
            option.addEventListener('click', function (e) {
                e.preventDefault();
                var preference = option.getAttribute('data-theme-preference');
                if (!preference) return;
                applyTheme(preference);
            });
        });
    });

    if (window.matchMedia) {
        var media = window.matchMedia('(prefers-color-scheme: dark)');
        var onChange = function () {
            if (getPreference() === 'system') {
                applyTheme('system');
            }
        };
        if (media.addEventListener) {
            media.addEventListener('change', onChange);
        } else if (media.addListener) {
            media.addListener(onChange);
        }
    }
})();
