(function () {
    document.querySelectorAll('.password-toggle-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.querySelector(button.getAttribute('data-target'));

            if (!input) {
                return;
            }

            var showing = input.type === 'text';

            input.type = showing ? 'password' : 'text';

            var icon = button.querySelector('.fas');

            if (icon) {
                icon.classList.toggle('fa-eye', showing);
                icon.classList.toggle('fa-eye-slash', !showing);
            }

            button.setAttribute('aria-pressed', showing ? 'false' : 'true');
            button.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });
})();
