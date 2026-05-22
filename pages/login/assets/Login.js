const recaptchaSiteKey = document.querySelector('meta[name="recaptcha-site-key"]')?.getAttribute('content');
function getRecaptchaToken(action) 
{
    return new Promise((resolve, reject) => 
    {
        if (!recaptchaSiteKey || typeof grecaptcha === 'undefined') 
        {
            reject(new Error('reCAPTCHA no está cargado'));
            return;
        }
        grecaptcha.ready(() => 
        {
            grecaptcha
            .execute(recaptchaSiteKey, { action })
            .then(resolve)
            .catch(reject);
        });
    });
}
function showAlert(message, type = 'danger') 
{
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    alertContainer.innerHTML = 
        `<div class="alert alert-${type} alert-dismissible fade show">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
}
function clearAlerts() 
{
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) { alertContainer.innerHTML = ''; }
}
function initPasswordToggles() 
{
    document.querySelectorAll('.toggle-password').forEach((button) => 
    {
        const inputId = button.dataset.target;
        const input = document.getElementById(inputId);
        const icon = button.querySelector('i');
        if (!input) return;
        const showPassword = () => 
        {
            input.type = 'text';
            icon?.classList.remove('bi-eye');
            icon?.classList.add('bi-eye-slash');
        };
        const hidePassword = () => 
        {
            input.type = 'password';
            icon?.classList.remove('bi-eye-slash');
            icon?.classList.add('bi-eye');
        };
        button.addEventListener('mousedown', showPassword);
        button.addEventListener('mouseup', hidePassword);
        button.addEventListener('mouseleave', hidePassword);
        button.addEventListener('touchstart', (e) => 
        {
            e.preventDefault();
            showPassword();
        });
        button.addEventListener('touchend', hidePassword);
        button.addEventListener('touchcancel', hidePassword);
    });
}
function fixRecaptchaBadge() 
{
    function fixBadge() 
    {
        const badge = document.querySelector('.grecaptcha-badge');
        if (badge) 
        {
            badge.style.right = '10px';
            badge.style.bottom = '10px';
            badge.style.visibility = 'visible';
            badge.style.opacity = '1';
            return true;
        }
        return false;
    }
    if (fixBadge()) return;
    const observer = new MutationObserver(() => {if (fixBadge()) { observer.disconnect(); }});
    observer.observe(document.body, 
    {
        childList: true,
        subtree: true
    });
    let attempts = 0;
    const interval = setInterval(() => {if (fixBadge() || attempts++ > 5) { clearInterval(interval); }}, 1000);
}
document.addEventListener('DOMContentLoaded', () => 
{
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    document.getElementById('toggle-register')?.addEventListener('click', (e) => 
    {
        e.preventDefault();
        clearAlerts();
        loginForm?.classList.add('d-none');
        registerForm?.classList.remove('d-none');
    });
    document.getElementById('toggle-login')?.addEventListener('click', (e) => 
    {
        e.preventDefault();
        clearAlerts();
        registerForm?.classList.add('d-none');
        loginForm?.classList.remove('d-none');
    });
    loginForm?.addEventListener('submit', async (e) => 
    {
        e.preventDefault();
        clearAlerts();
        let recaptchaToken = '';
        try {recaptchaToken = await getRecaptchaToken('login');} 
        catch (error) 
        {
            console.error('reCAPTCHA error:', error);
            showAlert('Error al verificar reCAPTCHA. Inténtalo de nuevo.');
            return;
        }
        const formData = new FormData(loginForm);
        formData.append('recaptcha_token', recaptchaToken);
        try 
        {
            const res = await fetch('/auth/login', 
            {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (res.ok && data.success) {window.location.href = data.redirect || '/pages/feed';} 
            else {showAlert(data.error || 'Credenciales incorrectas');}
        } 
        catch (err) 
        {
            console.error(err);
            showAlert('Error de conexión. Inténtalo más tarde.');
        }
    });
    let registerFormDataPending = null;
    async function submitRegisterForm(formData) 
{
    let recaptchaToken = '';

    try 
    {
        recaptchaToken = await getRecaptchaToken('register');
    } 
    catch (error) 
    {
        console.error('reCAPTCHA error:', error);
        showAlert('Error al verificar reCAPTCHA. Inténtalo de nuevo.');
        return;
    }

    formData.append('recaptcha_token', recaptchaToken);

    try 
    {
        const res = await fetch('/auth/register', 
        {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        const data = await res.json();

        if (res.ok && data.success) 
        {
            showAlert('Registro exitoso. Redirigiendo...', 'success');

            setTimeout(() => 
            {
                window.location.href = data.redirect || '/pages/feed';
            }, 1500);
        } 
        else 
        {
            showAlert(data.error || 'Error en el registro');
        }
    } 
    catch (err) 
    {
        console.error(err);
        showAlert('Error de conexión. Inténtalo más tarde.');
    }
}
    registerForm?.addEventListener('submit', async (e) => 
    {
        e.preventDefault();
        clearAlerts();
        const acceptTermsCheckbox = document.getElementById('acceptTerms');
        if (!acceptTermsCheckbox || !acceptTermsCheckbox.checked) 
        {
            showAlert
            (
                'Debes aceptar los Términos y Condiciones y la Política de Privacidad para registrarte.',
                'warning'
            );
            return;
        }
        const password = registerForm.querySelector('#register-password')?.value || '';
        const passwordConfirm = registerForm.querySelector('#register-password-confirm')?.value || '';
        if (password !== passwordConfirm) 
        {
            showAlert('Las contraseñas no coinciden.', 'warning');
            return;
        }
        const formData = new FormData(registerForm);
        formData.delete('contra_confirm');
        const username = formData.get('username');
        if (typeof username === 'string' && /\s/.test(username)) 
        {
            showAlert('El nombre de usuario no puede contener espacios.', 'warning');
            return;
        }
        registerFormDataPending = formData;
        const cookiesModalElement = document.getElementById('cookiesRegisterModal');
        if (!cookiesModalElement || typeof bootstrap === 'undefined') 
        {
            await submitRegisterForm(registerFormDataPending);
            registerFormDataPending = null;
            return;
        }
        const cookiesModal = bootstrap.Modal.getOrCreateInstance(cookiesModalElement);
        cookiesModal.show();
    });
    fixRecaptchaBadge();
    initPasswordToggles();
    document.getElementById('accept-cookies-register')?.addEventListener('click', async () => 
    {
        if (!registerFormDataPending) return;
        const cookiesModalElement = document.getElementById('cookiesRegisterModal');
        const cookiesModal = bootstrap.Modal.getOrCreateInstance(cookiesModalElement);
        cookiesModal.hide();
        await submitRegisterForm(registerFormDataPending);
        registerFormDataPending = null;
    });
    document.getElementById('reject-cookies-register')?.addEventListener('click', () => {registerFormDataPending = null;});
});