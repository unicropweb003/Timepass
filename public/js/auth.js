// Shared register/login handler. Submits the form to the JSON API via fetch,
// then shows inline validation errors or redirects on success.
(function () {
    const form = document.querySelector('#registerForm, #loginForm');
    if (!form) return;

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const submitBtn = form.querySelector('#submitBtn');
    const formError = form.querySelector('#formError');

    const clearErrors = () => {
        form.querySelectorAll('.error').forEach((el) => {
            el.textContent = '';
            el.classList.add('hidden');
        });
        if (formError) {
            formError.textContent = '';
            formError.classList.add('hidden');
        }
    };

    const showFieldError = (field, message) => {
        const el = form.querySelector(`.error[data-for="${field}"]`);
        if (el) {
            el.textContent = message;
            el.classList.remove('hidden');
        } else if (formError) {
            formError.textContent = message;
            formError.classList.remove('hidden');
        }
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        submitBtn.disabled = true;

        const payload = Object.fromEntries(new FormData(form).entries());

        try {
            const res = await fetch(window.AUTH_ENDPOINT, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(payload),
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                window.location.href = data.redirect || '/';
                return;
            }

            if (res.status === 422 && data.errors) {
                Object.entries(data.errors).forEach(([field, messages]) => {
                    showFieldError(field, messages[0]);
                });
            } else if (formError) {
                formError.textContent = data.message || 'Something went wrong. Please try again.';
                formError.classList.remove('hidden');
            }
        } catch (err) {
            if (formError) {
                formError.textContent = 'Network error. Please try again.';
                formError.classList.remove('hidden');
            }
        }

        submitBtn.disabled = false;
    });

    // Show / hide password buttons.
    form.querySelectorAll('[data-toggle-password]').forEach((btn) => {
        const input = btn.parentElement.querySelector('input');
        btn.addEventListener('click', () => {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.querySelector('[data-eye]').classList.toggle('hidden', show);
            btn.querySelector('[data-eye-off]').classList.toggle('hidden', !show);
        });
    });

    // Live password checklist (register page). Mirrors the server rules:
    // at least 8 characters with letters and numbers, plus a matching confirmation.
    const rules = form.querySelector('[data-password-rules]');
    if (rules) {
        const password = form.querySelector('#password');
        const confirmation = form.querySelector('#password_confirmation');
        const checks = {
            length: () => password.value.length >= 8,
            letter: () => /\p{L}/u.test(password.value),
            number: () => /\p{N}/u.test(password.value),
            match: () => password.value !== '' && password.value === confirmation.value,
        };
        const update = () => rules.querySelectorAll('[data-rule]').forEach((li) => {
            li.dataset.met = String(checks[li.dataset.rule]());
        });
        password.addEventListener('input', update);
        confirmation.addEventListener('input', update);
    }
})();
