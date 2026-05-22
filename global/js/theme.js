(function() // Módulo de gestión del tema (claro/oscuro/sistema) en la aplicación. Permite aplicar el tema seleccionado por el usuario, guardar su preferencia en localStorage, y responder a cambios en las preferencias del sistema.
{
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    function applyTheme(theme) // Aplica el tema seleccionado a la aplicación.
    {
        const isDark = (theme === 'oscuro') || (theme === 'sistema' && prefersDark);
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
    }
    let theme = 'sistema';
    if (window.userTheme && window.userTheme !== 'sistema') {theme = window.userTheme;}
    else
    {
        const stored = localStorage.getItem('theme');
        if (stored && stored !== 'sistema') {theme = stored;}
    }
    applyTheme(theme);
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () =>  // Si el usuario tiene la preferencia de tema en 'sistema', respondemos a los cambios en las preferencias del sistema aplicando el tema correspondiente.
    {
        const currentTheme = window.userTheme || localStorage.getItem('theme') || 'sistema';
        if (currentTheme === 'sistema') {applyTheme('sistema');}
    });
    window.setTheme = function(theme) // Función global para cambiar el tema desde cualquier parte del código. Aplica el tema seleccionado y guarda la preferencia en localStorage (a menos que sea 'sistema', en cuyo caso se elimina la preferencia guardada).
    {
        applyTheme(theme);
        if (theme !== 'sistema') {localStorage.setItem('theme', theme);}
        else {localStorage.removeItem('theme');}
    };
})();