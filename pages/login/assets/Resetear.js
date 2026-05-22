const form = document.getElementById('reset-form');
const alertContainer = document.getElementById('alert-container');
const recaptchaSiteKey = document.querySelector('meta[name="recaptcha-site-key"]')?.getAttribute('content');
function showAlert(message, type = 'danger') 
{
    if (!alertContainer) return;
    alertContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
}
function getRecaptchaToken(action) 
{
    return new Promise((resolve, reject) => 
    {
        if (!recaptchaSiteKey || typeof grecaptcha === 'undefined') {
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
form?.addEventListener('submit', async (e) => 
{
    e.preventDefault();
    const passwordInput = form.querySelector('input[name="datos[contrasena]"]');
    const confirmInput = form.querySelector('input[name="datos[contrasena1]"]');
    const password = passwordInput?.value || '';
    const confirm = confirmInput?.value || '';
    if (password !== confirm) 
    {
        showAlert('Las contraseñas no coinciden.');
        return;
    }
    if (password.length < 6) 
    {
        showAlert('La contraseña debe tener al menos 6 caracteres.');
        return;
    }
    let recaptchaToken = '';
    try {recaptchaToken = await getRecaptchaToken('password_reset_confirm');} 
    catch (error) 
    {
        console.error('reCAPTCHA error:', error);
        showAlert('Error al verificar reCAPTCHA. Recarga la página.');
        return;
    }
    let recaptchaInput = form.querySelector('input[name="recaptcha_token"]');
    if (!recaptchaInput) 
    {
        recaptchaInput = document.createElement('input');
        recaptchaInput.type = 'hidden';
        recaptchaInput.name = 'recaptcha_token';
        form.appendChild(recaptchaInput);
    }
    recaptchaInput.value = recaptchaToken;
    form.submit();
});
document.querySelectorAll('.toggle-password').forEach((button) => 
{
    const inputName = button.dataset.target;
    const input = document.querySelector(`input[name="${inputName}"]`);
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