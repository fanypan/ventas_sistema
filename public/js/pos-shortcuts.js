(function (window, $) {
    'use strict';

    var BROWSER_RESERVED = {
        F1: 1, F3: 1, F5: 1, F6: 1, F7: 1, F10: 1, F11: 1, F12: 1,
        'Ctrl+R': 1, 'Ctrl+T': 1, 'Ctrl+N': 1, 'Ctrl+W': 1, 'Ctrl+L': 1,
        'Ctrl+P': 1, 'Ctrl+F': 1, 'Ctrl+S': 1, 'Ctrl+H': 1, 'Ctrl+J': 1,
        'Ctrl+D': 1, 'Ctrl+K': 1, 'Alt+D': 1, 'Alt+F4': 1,
        'Alt+ArrowLeft': 1, 'Alt+ArrowRight': 1,
        'Ctrl+Shift+T': 1, 'Ctrl+Shift+N': 1, 'Ctrl+Shift+W': 1,
        'Ctrl+Shift+I': 1, 'Ctrl+Shift+J': 1, 'Ctrl+Shift+C': 1,
    };

    function isTypingTarget(el) {
        if (!el || el === document.body || el === document.documentElement) {
            return false;
        }
        if (el.isContentEditable) {
            return true;
        }
        var tag = (el.tagName || '').toLowerCase();
        if (tag === 'textarea' || tag === 'select') {
            return true;
        }
        if (tag !== 'input') {
            return false;
        }
        var type = (el.type || 'text').toLowerCase();
        return type !== 'button' && type !== 'submit' && type !== 'checkbox'
            && type !== 'radio' && type !== 'file' && type !== 'reset';
    }

    function normalizeKey(event) {
        var key = event.key;
        if (key === ' ') {
            return 'Space';
        }
        if (key === 'Esc') {
            return 'Escape';
        }
        if (key === '?' || key === '/') {
            return key;
        }
        if (key && key.length === 1) {
            return key.toUpperCase();
        }
        return key;
    }

    function chord(event) {
        var parts = [];
        if (event.ctrlKey || event.metaKey) {
            parts.push('Ctrl');
        }
        if (event.altKey) {
            parts.push('Alt');
        }
        var key = normalizeKey(event);
        if (event.shiftKey && key !== '?' && key !== '/') {
            parts.push('Shift');
        }
        parts.push(key);
        return parts.join('+');
    }

    function shortcutModalIsOpen() {
        return $('#modalPosShortcuts').hasClass('show');
    }

    window.initPosShortcuts = function (options) {
        options = options || {};
        var actions = options.actions || {};
        var allowWhenTyping = options.allowWhenTyping || [];
        var ns = '.posShortcuts';

        $(document).off('keydown' + ns).on('keydown' + ns, function (event) {
            if (event.repeat) {
                return;
            }

            var name = chord(event);
            if (BROWSER_RESERVED[name]) {
                return;
            }

            var handler = actions[name];
            if (!handler) {
                return;
            }

            if (shortcutModalIsOpen() && name !== '?' && name !== 'Alt+Shift+H') {
                return;
            }

            if (isTypingTarget(event.target) && allowWhenTyping.indexOf(name) === -1) {
                return;
            }

            event.preventDefault();
            handler();
        });
    };
})(window, jQuery);
