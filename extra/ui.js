(function(){
  const body = document.body;

  // Tema salvato nel cookie (solo dark/light)
  const match = document.cookie.match(/(?:^|;\s*)theme=(theme-(dark|light))/);
  const saved = match ? match[1] : 'theme-dark';

  body.classList.remove('theme-dark','theme-light');
  body.classList.add(saved);

  // Toggle su dark <-> light
  document.addEventListener('click', (e)=>{
    const btn = e.target.closest('#themeToggle');
    if(!btn) return;

    const next = body.classList.contains('theme-dark') ? 'theme-light' : 'theme-dark';

    body.classList.remove('theme-dark','theme-light');
    body.classList.add(next);

    // salva scelta
    document.cookie = `theme=${next};path=/;max-age=${60*60*24*365}`;
  });

  // Grafico mock
  function sparkline(canvas){
    if(!canvas || !canvas.getContext) return;
    const ctx = canvas.getContext('2d');
    const w = canvas.width = canvas.clientWidth;
    const h = canvas.height = canvas.clientHeight;
    const n = 32;
    ctx.lineWidth = 2;
    ctx.beginPath();
    for(let i=0;i<n;i++){
      const x = i/(n-1)*w;
      const y = h*0.2 + Math.abs(Math.sin(i*0.6))*h*0.6;
      i===0 ? ctx.moveTo(x,y) : ctx.lineTo(x,y);
    }
    ctx.stroke();
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('canvas[data-mock]').forEach(sparkline);
  });

})();
  