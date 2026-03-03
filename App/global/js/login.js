document.addEventListener('DOMContentLoaded', () => 
{
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const toggleRegister = document.getElementById("toggle-register");
    const toggleLogin = document.getElementById("toggle-login");
    const alertContainer = document.getElementById("alert-container");

    const updateTheme = () => 
    {
        const theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', theme);
    };

    updateTheme();
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', updateTheme);

    //Alerta dinámica según lo que sea.
    const showAlert = (message, type = 'danger') => 
    {
        if (!alertContainer) return;
        alertContainer.innerHTML = `
            <div class="alert alert-${type} alert-dismissible fade show border-0 shadow-sm" role="alert" style="font-size: 0.85rem;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    };
    //Limpia la caja de alertas.
    const clearAlerts = () => {if (alertContainer) alertContainer.innerHTML = '';};

    //Interruptor del formulario entre login y registro.
    if (toggleRegister) 
    {
        toggleRegister.addEventListener("click", (e) => 
        {
            e.preventDefault();
            clearAlerts();
            loginForm.classList.add("d-none");
            registerForm.classList.remove("d-none");
        });
    }

    if (toggleLogin) 
    {
        toggleLogin.addEventListener("click", (e) => 
        {
            e.preventDefault();
            clearAlerts();
            registerForm.classList.add("d-none");
            loginForm.classList.remove("d-none");
        });
    }

    //Manejo de envíos de formularios para login y registro con validaciones y muestra de alertas según el resultado.
    const handleFormSubmit = (form, url, successRedirect) => 
    {
        form.addEventListener('submit', async (e) => 
        {
            e.preventDefault();
            clearAlerts();
            
            const formData = new FormData(form);
            if (form.id === "register-form") 
            {
                const username = formData.get('username');
                const nombre = formData.get('nombre');
                if (/\s/.test(username)) {return showAlert('El nombre de usuario (login) no puede contener espacios.', 'warning');}
                if (username.length < 4) {return showAlert('El nombre de usuario debe tener al menos 4 caracteres.', 'warning');}
            }
            try 
            {
                const response = await fetch(url, { method: 'POST', body: formData });
                const result = await response.json();
                
                if (result.success) 
                {
                    if (successRedirect) {window.location.href = successRedirect;} 
                    else 
                    {
                        showAlert('¡Cuenta creada con éxito! Ya puedes entrar.', 'success');
                        form.reset();
                        setTimeout(() => toggleLogin.click(), 2000);
                    }
                } 
                else 
                {
                    showAlert(result.error || 'Ocurrió un error inesperado', 'danger');
                }
            } 
            catch (error) 
            {
                showAlert('Error crítico: No se pudo comunicar con el servidor', 'danger');
            }
        });
    };

    if (loginForm) handleFormSubmit(loginForm, '/App/auth/login', '/App/');
    if (registerForm) handleFormSubmit(registerForm, '/App/auth/register', null); //No redirige automáticamente tras el registro, solo muestra mensaje y cambia al login.
});