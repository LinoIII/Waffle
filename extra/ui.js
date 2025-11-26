document.addEventListener('DOMContentLoaded', () => {
    const toggleBtn = document.getElementById('themeToggle');
    const body = document.body;

    // 1. Applica tema salvato all'avvio
    const currentTheme = localStorage.getItem('theme') || 'dark';
    body.setAttribute('data-theme', currentTheme);

    // 2. Gestione Click
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const oldTheme = body.getAttribute('data-theme');
            const newTheme = oldTheme === 'light' ? 'dark' : 'light';

            // Applica
            body.setAttribute('data-theme', newTheme);
            
            // Salva
            localStorage.setItem('theme', newTheme);
            // Salva anche cookie per PHP (opzionale ma utile)
            document.cookie = `theme=${newTheme};path=/;max-age=31536000`;
        });
    }

    // 3. Sparklines (Grafici finti per estetica)
    document.querySelectorAll('canvas[data-mock]').forEach(canvas => {
        const ctx = canvas.getContext('2d');
        const w = canvas.width = canvas.clientWidth;
        const h = canvas.height = canvas.clientHeight;
        
        // Colore linea basato sul CSS
        const style = getComputedStyle(document.body);
        const color = style.getPropertyValue('--primary').trim() || '#3b82f6';

        ctx.strokeStyle = color;
        ctx.lineWidth = 2;
        ctx.beginPath();
        
        // Disegna onda casuale
        for(let i=0; i<w; i+=5) {
            const y = (h/2) + Math.sin(i * 0.05) * (h * 0.3) + (Math.random() * 5);
            i===0 ? ctx.moveTo(i,y) : ctx.lineTo(i,y);
        }
        ctx.stroke();
    });
});