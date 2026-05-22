"use strict";

document.addEventListener('DOMContentLoaded', function() {

    const inputImagen = document.getElementById('inputImagen');
    const contenedorVistaPrevia = document.getElementById('contenedorVistaPrevia');
    const imagenVistaPrevia = document.getElementById('imagenVistaPrevia');
    const avatarVistaPrevia = document.getElementById('avatarVistaPrevia');
    const form = document.getElementById('formEditarAvatar');
    const btnSeleccionarImagen = document.getElementById('btnSeleccionarImagen');

    // permite que al hacer click en el boton que se habra el input file que esta oculto
    btnSeleccionarImagen.addEventListener('click', function() {
        inputImagen.click();
    });

    
    inputImagen.addEventListener('change', function(e) {

        const file = e.target.files[0];

        if (file) {

            // limita el tamaño del archivo a 2MB
            if (file.size > 2 * 1024 * 1024) {

                window.Alertas.error('La imagen es demasiado grande. El tamaño máximo permitido es de 2MB.');
                inputImagen.value = '';
                return;

            }
            
            // limita el tipo de archivo a imágenes
            if (!file.type.startsWith('image/')) {

                window.Alertas.error('Archivo no válido. Por favor, selecciona una imagen.');
                inputImagen.value = '';
                return;

            }

            // vista previa de la imagen seleccionada
            const reader = new FileReader();

            reader.addEventListener('load', function(e) {

                if (avatarVistaPrevia) {

                    avatarVistaPrevia.remove();
                }
                
                let img = contenedorVistaPrevia.querySelector('img');

                if (!img) {

                    img = document.createElement('img');
                    img.id = 'imagenVistaPrevia';
                    img.classList.add('w-100', 'h-100', 'object-cover');
                    img.alt = 'Foto de perfil';

                    contenedorVistaPrevia.appendChild(img);

                }
                img.src = e.target.result;
            });
            // lee el archivo como una URL de datos para mostrar la vista previa
            reader.readAsDataURL(file);

        }
    });

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const file = inputImagen.files[0];

        if (!file) {

            window.Alertas.error('Por favor, selecciona una imagen para subir.');
            return;

        }

        // empaqueta el archivo en un FormData para enviarlo al servidor
        const formData = new FormData();

        formData.append('foto', file);

        const csrfTokenInput = document.querySelector('#formEditarAvatar input[name="csrf_token"]');
        const csrfToken = csrfTokenInput ? csrfTokenInput.value : '';
        
        formData.append('csrf_token', csrfToken);

        try {

            // enviamos
            const response = await fetch('/pages/administracion/subir-foto', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.status === 'success') {

                const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarAvatar'));
                modal.hide();
                
                document.getElementById('imgPerfilConfig').src=data.ruta;
                document.getElementById('imgPerfil').src= data.ruta;
                

                const modalConfig=bootstrap.Modal.getInstance(document.getElementById('modalConfigAdmin'));
                modalConfig.show();
                
                
            } else {
               
                window.Alertas.error(data.message || 'Error al subir la imagen: ' + (data.error || ''));
            }
        } catch (error) {
            
            console.error('Error:', error);
            window.Alertas.error('Error al subir la imagen: ' + error.message);
        }
    });
});