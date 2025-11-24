(function(){
  const body = document.body;
  const toggleBtn = document.getElementById('themeToggle');

  // Gestione Tema
  if(toggleBtn){
    toggleBtn.addEventListener('click', () => {
      const current = body.getAttribute('data-theme');
      const next = current === 'light' ? 'dark' : 'light';
      
      body.setAttribute('data-theme', next);
      // Salva nel cookie per 1 anno così PHP lo legge al prossimo reload
      document.cookie = `theme=${next};path=/;max-age=${60*60*24*365}`;
    });
  }

  // Funzione mock per grafici sparkline (Solo visuale)
  function drawSparkline(canvas){
    if(!canvas || !canvas.getContext) return;
    const ctx = canvas.getContext('2d');
    const w = canvas.width = canvas.clientWidth;
    const h = canvas.height = canvas.clientHeight;
    
    // Configurazione linea
    ctx.strokeStyle = getComputedStyle(body).getPropertyValue('--accent').trim() || '#3b82f6';
    ctx.lineWidth = 2;
    ctx.beginPath();
    
    // Genera dati casuali ma coerenti
    const points = 20;
    for(let i=0; i<points; i++){
      const x = (i / (points-1)) * w;
      // Onda casuale
      const noise = Math.random() * (h * 0.3);
      const y = (h * 0.5) + (Math.sin(i) * (h * 0.2)) - (noise * 0.5);
      
      i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
    }
    ctx.stroke();
    
    // Sfumatura sotto la linea
    ctx.lineTo(w, h);
    ctx.lineTo(0, h);
    ctx.fillStyle = ctx.strokeStyle + '1a'; // 10% opacità
    ctx.fill();
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('canvas[data-mock]').forEach(drawSparkline);
  });

})();