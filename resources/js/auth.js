import './bootstrap';
import axios from 'axios';

// Send the CSRF token with every request so Laravel accepts the POST.
const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrf) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
}
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const form = document.querySelector('#registerForm, #loginForm');
if (form) {
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
            const { data } = await axios.post(window.AUTH_ENDPOINT, payload);
            window.location.href = data.redirect || '/';
        } catch (error) {
            const res = error.response;
            if (res && res.status === 422 && res.data.errors) {
                Object.entries(res.data.errors).forEach(([field, messages]) => {
                    showFieldError(field, messages[0]);
                });
            } else if (formError) {
                formError.textContent =
                    res?.data?.message || 'Something went wrong. Please try again.';
                formError.classList.remove('hidden');
            }
            submitBtn.disabled = false;
        }
    });
}
