(function () {
    'use strict';

    function initializeInstaller() {
        var form = document.getElementById('setupform');
        var resetConfirmation = document.getElementById('confirm_reset');
        var resetButton = document.getElementById('installer-reset-button');

        if (resetConfirmation && resetButton) {
            var syncResetButton = function () {
                var enabled = resetConfirmation.checked;
                resetButton.disabled = !enabled;
                resetButton.classList.toggle('disabled', !enabled);
                resetButton.setAttribute('aria-disabled', enabled ? 'false' : 'true');
            };

            resetConfirmation.addEventListener('change', syncResetButton);
            syncResetButton();
        }

        if (form) {
            form.addEventListener('submit', function () {
                form.classList.add('is-submitting');
                form.setAttribute('aria-busy', 'true');
            });
        }

        var error = document.querySelector('.installer-content .alert-danger, .installer-content .alert-error');
        if (error) {
            error.setAttribute('tabindex', '-1');
            error.focus({preventScroll: false});
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeInstaller);
    } else {
        initializeInstaller();
    }
}());
