import { logout } from '/global/js/auth.js';

const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', async () => {
        try {
            const result = await logout();
            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            } else {
                window.location.href = '../login/';
            }
        } catch (error) {
            console.error('Error en logout:', error);
            window.location.href = '../login/';
        }
    });
}

document.getElementById('configForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    formData.append('csrf_token', csrfToken);

    const alertDiv = document.getElementById('alert');
    alertDiv.classList.add('d-none');

    try {
        const res = await fetch('/configuracion/guardar', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            localStorage.removeItem('theme');
            alertDiv.className = 'alert rounded-4 alert-success';
            alertDiv.innerHTML = `<i class="bi bi-check-circle me-1"></i> ${data.message}`;
            alertDiv.classList.remove('d-none');
            setTimeout(() => location.reload(), 1000);
        } else {
            alertDiv.className = 'alert rounded-4 alert-danger';
            alertDiv.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> ${data.message || 'Error al guardar'}`;
            alertDiv.classList.remove('d-none');
        }
    } catch (err) {
        alertDiv.className = 'alert rounded-4 alert-danger';
        alertDiv.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> Error de conexion`;
        alertDiv.classList.remove('d-none');
    }
});
