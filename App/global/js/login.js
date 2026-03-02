document.addEventListener('DOMContentLoaded', () => 
{
    //Elementos del DOM
    const loginForm = document.getElementById("login-form");
    const registerForm = document.getElementById("register-form");
    const toggleRegister = document.getElementById("toggle-register");
    const toggleLogin = document.getElementById("toggle-login");
    const title = document.getElementById("form-title");
    const alertContainer = document.getElementById("alert-container");

    const showAlert = (message, type = 'danger') => 
    {
        if (!alertContainer) return;
        alertContainer.innerHTML = 
            `<div class="alert alert-${type} alert-dismissible fade show" role="alert" style="font-size: 0.9rem;">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
    };

    //Lógica pa cambiar Login y Registro
    if (toggleRegister) 
    {
        toggleRegister.addEventListener("click", (e) => {
            e.preventDefault();
            loginForm.classList.add("d-none");
            registerForm.classList.remove("d-none");
            title.textContent = "Crear Cuenta";
            if (alertContainer) alertContainer.innerHTML = ''; 
        });
    }

    if (toggleLogin) 
    {
        toggleLogin.addEventListener("click", (e) => {
            e.preventDefault();
            registerForm.classList.add("d-none");
            loginForm.classList.remove("d-none");
            title.textContent = "Iniciar Sesión";
            if (alertContainer) alertContainer.innerHTML = ''; 
        });
    }
    //Para manejar los AJAX.
    const handleFormSubmit = async (form, url, successRedirect = null) => 
    {
        form.addEventListener('submit', async (e) => 
        {
            e.preventDefault();
            const formData = new FormData(form);
            try 
            {
                const response = await fetch(url, 
                {
                    method: 'POST',
                    body: formData
                });
                const contentType = response.headers.get("content-type");
                
                if (contentType && contentType.includes("application/json")) 
                {
                    const result = await response.json();
                    
                    if (result.success) 
                    {
                        if (successRedirect) {window.location.href = successRedirect;} 
                        else 
                        {
                            showAlert('¡Cuenta creada correctamente! Ya puedes iniciar sesión.', 'success');
                            form.reset();
                            setTimeout(() => toggleLogin.click(), 2000);
                        }
                    } 
                    else {showAlert(result.error || 'Ocurrió un error inesperado');}
                } 
                else 
                {
                    const errorText = await response.text();
                    showAlert(errorText || 'Error crítico en el servidor');
                }

            } 
            catch (error) 
            {
                console.error("Error en Fetch:", error);
                showAlert('No se pudo conectar con el servidor. Inténtalo de nuevo.');
            }
        });
    };
    if (loginForm) {handleFormSubmit(loginForm, '/App/auth/login', '/App/');}
    if (registerForm) {handleFormSubmit(registerForm, '/App/auth/register', null);}
});