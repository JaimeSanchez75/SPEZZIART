/*Iniciación del Arrastre*/
document.addEventListener('DOMContentLoaded', () => 
{
    document.querySelectorAll('.comments-overlay').forEach(overlay => 
    {
        setupDrag(overlay);
    });
});

function setupDrag(overlay) 
{
    const sheet = overlay.querySelector('.comments-sheet');
    if (!sheet) 
    {
        console.log('Sheet no encontrado');
        return;
    }

    let startY = 0;
    let dragging = false;

    /* Inicio del Arrastre */
    sheet.addEventListener('touchstart', (e) => 
    {
        startY = e.touches[0].clientY;
        dragging = true;
    });
    /* Dirección del Arrastre */
    sheet.addEventListener('touchmove', (e) => 
    {
        if (!dragging) return;
        const diff = e.touches[0].clientY - startY;
        if (diff > 0) 
        {
            e.preventDefault();
            sheet.style.transform = `translateY(${diff}px)`;
        }
    });
    /* Final del Arrastre */
    sheet.addEventListener('touchend', (e) => 
    {
        if (!dragging) return;
        dragging = false;
        
        const diff = e.changedTouches[0].clientY - startY;
        
        if (diff > 100) 
        {
            closeComments(overlay.id.split('-')[1]);
        } 
        else 
        {
            sheet.style.transform = '';
        }
    });
}