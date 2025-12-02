document.addEventListener('DOMContentLoaded', function () {
  function setupToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);

    if (!toggle || !input) return;

    toggle.addEventListener('click', function () {
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';

      toggle.setAttribute('aria-pressed', String(isPassword));
      toggle.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
      toggle.setAttribute('title', isPassword ? 'Hide password' : 'Show password');

      const icon = toggle.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      }
    });

    toggle.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggle.click();
      }
    });
  }

  // Apply to both password fields
  setupToggle('togglePassword', 'login-pword');
  setupToggle('toggleConfirmPassword', 'login-confirm-pword');
});
