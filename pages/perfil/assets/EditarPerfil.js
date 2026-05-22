document.addEventListener('DOMContentLoaded', function() {
    const isOwnProfile = window.isOwnProfile === true;
    if (!isOwnProfile) return;
    const MAX_NOMBRE_LENGTH = 30;

    const nombreInput = document.getElementById('editNombreInput');
    const nombreCounter = document.getElementById('editNombreCounter');

    function actualizarContadorNombre() 
    {
        if (!nombreInput || !nombreCounter) return;
        const usados = nombreInput.value.length;
        nombreCounter.textContent = `${usados}/${MAX_NOMBRE_LENGTH}`;
        nombreCounter.classList.toggle('texto-rojo', usados >= MAX_NOMBRE_LENGTH);
        nombreCounter.classList.toggle('text-secondary', usados < MAX_NOMBRE_LENGTH);
    }
    if (nombreInput) 
    {
        nombreInput.setAttribute('maxlength', MAX_NOMBRE_LENGTH);
        nombreInput.addEventListener('input', actualizarContadorNombre);
        actualizarContadorNombre();
    }
    const nameForm = document.getElementById('editNameForm');
    if (nameForm) {
        nameForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(nameForm);
            const nombre = (formData.get('nombre') || '').trim();
            if (nombre.length > MAX_NOMBRE_LENGTH) {
                errorDiv.textContent = `El nombre no puede superar los ${MAX_NOMBRE_LENGTH} caracteres`;
                errorDiv.classList.remove('d-none');
                return;
            }
            const errorDiv = document.getElementById('nameError');
            errorDiv.classList.add('d-none');

            try {
                const res = await fetch('/pages/perfil/actualizar-nombre', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    document.getElementById('userDisplayName').innerText = '@' + formData.get('nombre');
                    bootstrap.Modal.getInstance(document.getElementById('editNameModal')).hide();
                } else {
                    errorDiv.textContent = data.message || 'Error al actualizar';
                    errorDiv.classList.remove('d-none');
                }
            } catch (err) {
                errorDiv.textContent = 'Error de conexion';
                errorDiv.classList.remove('d-none');
            }
        });
    }

    const avatarForm = document.getElementById('editAvatarForm');
    const avatarPreview = document.getElementById('avatarPreview');
    const avatarInput = avatarForm?.querySelector('input[name="foto"]');

    if (avatarInput) {
        avatarInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (ev) => {
                avatarPreview.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    if (avatarForm) {
        avatarForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(avatarForm);
            const errorDiv = document.getElementById('avatarError');
            const successDiv = document.getElementById('avatarSuccess');
            errorDiv.classList.add('d-none');
            successDiv.classList.add('d-none');

            try {
                const res = await fetch('/pages/perfil/subir-foto', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.status === 'success') {
                    const avatarImg = document.querySelector('#profileAvatar');
                    const placeholder = document.getElementById('profileAvatarPlaceholder');

                    if (avatarImg) {
                        avatarImg.src = data.ruta + '?t=' + Date.now();
                    } else if (placeholder) {
                        const newImg = document.createElement('img');
                        newImg.id = 'profileAvatar';
                        newImg.src = data.ruta + '?t=' + Date.now();
                        newImg.className = 'rounded-circle border border-3 border-white shadow';
                        newImg.width = 120;
                        newImg.height = 120;
                        newImg.style.objectFit = 'cover';
                        placeholder.parentNode.replaceChild(newImg, placeholder);
                    }

                    successDiv.textContent = 'Foto actualizada correctamente';
                    successDiv.classList.remove('d-none');
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('editAvatarModal')).hide();
                    }, 1000);
                } else {
                    errorDiv.textContent = data.message || 'Error al subir foto';
                    errorDiv.classList.remove('d-none');
                }
            } catch (err) {
                errorDiv.textContent = 'Error de conexion';
                errorDiv.classList.remove('d-none');
            }
        });
    }

    const bannerModal = document.getElementById('editBannerModal');
    if (bannerModal) {
        bannerModal.addEventListener('show.bs.modal', async () => {
            const grid = document.getElementById('bannersGrid');
            grid.innerHTML = '<div class="col-12 text-center">Cargando banners...</div>';

            try {
                const res = await fetch('/pages/perfil/banners-todos');
                const data = await res.json();

                if (!data.banners || !data.banners.length) {
                    grid.innerHTML = '<div class="col-12 text-center">No hay banners disponibles</div>';
                    return;
                }

                grid.innerHTML = '';
                data.banners.forEach((banner) => {
                    const col = document.createElement('div');
                    col.className = 'col-6 col-md-4 col-lg-3';
                    const desbloqueado = banner.desbloqueado > 0;

                    col.innerHTML = `
                        <div class="banner-card card border sombra rounded rounded-4 p-2 efectoEscala ${desbloqueado ? '' : 'bloqueado'}" data-id="${banner.ID_Banner}" data-url="${banner.ImagenURL}">
                            <div class="banner-preview rounded-4" style="background-image: url('${banner.ImagenURL}'); height: 120px; background-size: cover; background-position: center; position: relative;">
                                ${!desbloqueado ? '<div class="lock-overlay"><span class="material-symbols-outlined text-muted">lock</span></div>' : ''}
                            </div>
                            <div class="banner-name mt-2 text-center texto fw-bold">${banner.Nombre}</div>
                            ${!desbloqueado ? '<div class="text-center text-muted small">Bloqueado</div>' : ''}
                        </div>
                    `;

                    if (desbloqueado) {
                        const card = col.querySelector('.banner-card');
                        card.style.cursor = 'pointer';
                        card.addEventListener('click', async () => {
                            const formData = new FormData();
                            formData.append('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
                            formData.append('banner_id', card.dataset.id);

                            try {
                                const resBanner = await fetch('/pages/perfil/cambiar-banner', { method: 'POST', body: formData });
                                const dataBanner = await resBanner.json();

                                if (dataBanner.status === 'success') {
                                    document.querySelector('.header-perfil').style.backgroundImage = `url('${card.dataset.url}')`;
                                    bootstrap.Modal.getInstance(bannerModal).hide();
                                } else {
                                    window.Alertas.error(dataBanner.message || 'Error al cambiar banner');
                                }
                            } catch (err) {
                                console.error(err);
                                window.Alertas.error('Error de conexion');
                            }
                        });
                    } else {
                        col.querySelector('.banner-preview').style.filter = 'grayscale(0.8)';
                        col.querySelector('.banner-preview').style.opacity = '0.6';
                    }

                    grid.appendChild(col);
                });
            } catch (err) {
                grid.innerHTML = '<div class="col-12 text-center text-danger">Error al cargar banners</div>';
            }
        });
    }
});
