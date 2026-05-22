(function() // Módulo de registro de eventos en la aplicación. Permite registrar eventos con diferentes niveles (INFO, ERROR, AVISO, OK) desde cualquier parte del código, mostrando los mensajes en la consola del navegador y enviándolos al servidor para su almacenamiento y análisis posterior.
{
    // Lista de mensajes pendientes de enviar al servidor
    let mensajesPendientes = [];
    // Temporizador para agrupar varios mensajes y enviarlos de una sola vez
    let temporizadorLote = null;
    function enviarMensajesPendientes() // Envía los mensajes pendientes al servidor y limpia la cola. Se llama automáticamente después de un tiempo o al cerrar la página.
    {
        if (mensajesPendientes.length === 0) return;
        // Copiar los mensajes actuales y vaciar la cola
        const mensajesParaEnviar = [...mensajesPendientes];
        mensajesPendientes = [];
        // Enviar al servidor (sin esperar respuesta, para no bloquear)
        fetch('/api/log-frontend', 
        {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ logs: mensajesParaEnviar }),
            keepalive: true   // Permite que la petición termine aunque se cierre la página
        }).catch(error => {console.warn('Error al enviar logs al servidor:', error);});
    }
    /**
     * Registra un mensaje localmente y programa su envío al servidor.
     * @param {string} nivel   - Nivel: 'INFO', 'ERROR', 'AVISO', 'OK'
     * @param {string} archivo - Nombre del archivo JS (ej: "login.js")
     * @param {string} funcion - Nombre de la función o método
     * @param {string} mensaje - Texto descriptivo del evento
     */
    function registrarYEnviar(nivel, archivo, funcion, mensaje) // Registra el mensaje en la consola y lo programa para enviar al servidor.
    {
        // Obtener fecha y hora local formateada
        const ahora = new Date();
        const fechaHora = `${ahora.getDate().toString().padStart(2,'0')}-${(ahora.getMonth()+1).toString().padStart(2,'0')}-${ahora.getFullYear()} ${ahora.getHours().toString().padStart(2,'0')}:${ahora.getMinutes().toString().padStart(2,'0')}:${ahora.getSeconds().toString().padStart(2,'0')}`;
        // Mostrar en la consola del navegador
        const lineaConsola = `[${fechaHora}] - ${archivo} - ${funcion} - ${nivel} - ${mensaje}`;
        console.log(lineaConsola);
        // Guardar en la cola para enviarlo más tarde
        mensajesPendientes.push({ nivel, archivo, funcion, mensaje });
        // Programar el envío si no hay ya un temporizador activo
        if (temporizadorLote !== null) clearTimeout(temporizadorLote);
        temporizadorLote = setTimeout(enviarMensajesPendientes, 2000); // Espera 2 segundos
    }
    /**
     * Función global para registrar eventos desde cualquier archivo de la aplicación.
     * @param {string} nivel   - Nivel: 'INFO', 'ERROR', 'AVISO', 'OK'
     * @param {string} archivo - Nombre del archivo JS
     * @param {string} funcion - Nombre de la función/método
     * @param {string} mensaje - Mensaje
     */
    window.registroApp = function(nivel, archivo, funcion, mensaje)  // Función global para registrar eventos desde cualquier parte del código. Normaliza el nivel y delega en registrarYEnviar.
    {
        // Asegurar que el nivel esté en mayúsculas y sea válido
        const nivelNormalizado = nivel.toUpperCase();
        const nivelesValidos = ['INFO', 'ERROR', 'AVISO', 'OK'];
        const nivelFinal = nivelesValidos.includes(nivelNormalizado) ? nivelNormalizado : 'INFO';
        registrarYEnviar(nivelFinal, archivo, funcion, mensaje);
    };
    // Al cerrar la página, enviar los mensajes pendientes inmediatamente
    window.addEventListener('beforeunload', function()  // Este evento se dispara cuando la página está a punto de cerrarse o recargarse. Es el momento ideal para enviar cualquier mensaje pendiente al servidor, ya que el usuario está a punto de irse.
    {
        if (mensajesPendientes.length > 0) {enviarMensajesPendientes();}
    });
})();