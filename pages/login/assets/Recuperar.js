const form = document.getElementById('recuperar-form');
const recaptchaSiteKey = document.querySelector('meta[name="recaptcha-site-key"]')?.getAttribute('content');
function showRecuperarAlert(message) 
{
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;
    alertContainer.innerHTML = `<div class="alert alert-danger">${message}</div>`;
}
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
form?.addEventListener('submit', async (e) => 
{
    e.preventDefault();
    let recaptchaToken = '';
    try {recaptchaToken = await getRecaptchaToken('password_reset');} 
    catch (error) 
    {
        console.error('reCAPTCHA error:', error);
        showRecuperarAlert('Error al verificar reCAPTCHA. Recarga la página.');
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