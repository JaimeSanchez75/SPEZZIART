document.addEventListener('DOMContentLoaded', () => 
{
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    // LOGIN
    loginForm.addEventListener('submit', async (e) => 
    {
        e.preventDefault();
        const formData = new FormData(loginForm);
        const res = await fetch('/controllers/AuthController.php?action=login', 
        {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Importante para cookies!!!!
        });
        const data = await res.json();
        if (res.ok) 
        {
            window.Alertas.exito('Login correcto!');
            window.location.href = '/dashboard';
        } 
        else {window.Alertas.error(data.error || 'Error en login');}
    });
    // REGISTER
    registerForm.addEventListener('submit', async (e) => 
    {
        e.preventDefault();
        const formData = new FormData(registerForm);
        formData.append('csrf_token', window.csrf_token);
        const res = await fetch('/controllers/AuthController.php?action=register', 
        {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });
        const data = await res.json();
        if (res.ok) 
        {
            window.Alertas.exito('Registro correcto!');
            window.location.href = '/login';
        } 
        else {window.Alertas.error(data.error || 'Error en registro');}
    });
});