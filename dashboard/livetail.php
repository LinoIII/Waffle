<?php
// FILE: dashboard/livetail.php
require_once '../config.php';
require_once '../functions.php';

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$pageTitle = 'Live Log Stream';
require_once '../header.php';
?>

<style>
    /* Stile Toolbar Locale */
    .live-controls {
        display: flex; align-items: center; gap: 15px; padding: 12px 20px;
        background-color: var(--bg-card); border-bottom: 1px solid var(--border);
    }
    /* Input specifici per il live */
    .live-input {
        background: var(--bg-input); border: 1px solid var(--border); color: var(--text-main);
        padding: 8px 12px; border-radius: var(--radius); font-size: 0.9rem; width: 250px; outline: none;
        font-family: var(--font-mono); /* Font mono per IP */
    }
    .live-input:focus { border-color: var(--primary); box-shadow: 0 0 0 2px rgba(31, 111, 235, 0.2); }
    
    .live-btn {
        background: transparent; border: 1px solid var(--border); color: var(--text-muted);
        padding: 8px 16px; border-radius: var(--radius); cursor: pointer; font-size: 0.85rem; font-weight: 600;
        transition: all 0.2s;
    }
    .live-btn:hover { background: rgba(255,255,255,0.05); color: var(--text-main); border-color: var(--text-muted); }
    .live-btn.active { background: rgba(248,81,73,0.15); color: var(--danger); border-color: var(--danger); }
    
    /* Indicatore Live */
    .live-indicator { display: inline-block; width: 8px; height: 8px; background-color: #3fb950; border-radius: 50%; margin-right: 8px; }
    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0.3; } }
</style>

<div class="main-container">
    <div class="card full-width" style="display: flex; flex-direction: column; height: 80vh; min-height: 600px;">
        
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center;">
                <span class="live-indicator blink" id="live-dot"></span> 
                <span style="font-weight:700; letter-spacing:0.5px;">LIVE TRAFFIC STREAM</span>
            </div>
            <div>
                <span id="status-badge" class="badge badge-low">CONNECTED</span>
            </div>
        </div>
        
        <div class="live-controls">
            <input type="text" id="filter-ip" class="live-input" placeholder="Filtra per IP o Stringa...">

            <select id="filter-type" class="live-input" style="width: 180px; font-family: var(--font-sans);">
                <option value="">Tutti i Tipi</option>
                <option value="SQL Injection">SQL Injection</option>
                <option value="XSS Attempt">XSS Attempt</option>
                <option value="Directory Traversal">Directory Traversal</option>
                <option value="Bad User Agent">Bad User Agent</option>
            </select>

            <div style="flex: 1;"></div> <button id="btn-pause" class="live-btn" onclick="togglePause()">⏸ Pausa Flusso</button>
            <button class="live-btn" onclick="clearTerminal()">🗑 Pulisci</button>
        </div>

        <div class="card-body" style="background-color: #0d1117; padding: 0; flex: 1; overflow: hidden; position: relative;">
            <div id="terminal-window" style="height: 100%; overflow-y: auto; padding: 20px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; color: #c9d1d9; scroll-behavior: smooth;">
                <div class="log-line" style="color:#8b949e; border-bottom: 1px dashed #30363d; padding-bottom: 5px; margin-bottom: 10px;">
                    // WAF-FLE Live Stream Console v2.1<br>
                    // Waiting for incoming events...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const terminal = document.getElementById('terminal-window');
    const filterIpInput = document.getElementById('filter-ip');
    const filterTypeInput = document.getElementById('filter-type');
    const btnPause = document.getElementById('btn-pause');
    const statusBadge = document.getElementById('status-badge');
    const liveDot = document.getElementById('live-dot');
    
    let isPaused = false;

    function togglePause() {
        isPaused = !isPaused;
        if(isPaused) {
            btnPause.innerHTML = "▶ Riprendi Flusso";
            btnPause.classList.add('active');
            statusBadge.innerText = 'PAUSED';
            statusBadge.className = 'badge badge-medium'; // Giallo
            liveDot.classList.remove('blink');
            liveDot.style.backgroundColor = '#d29922';
        } else {
            btnPause.innerHTML = "⏸ Pausa Flusso";
            btnPause.classList.remove('active');
            statusBadge.innerText = 'CONNECTED';
            statusBadge.className = 'badge badge-low'; // Verde
            liveDot.classList.add('blink');
            liveDot.style.backgroundColor = '#3fb950';
        }
    }

    function clearTerminal() {
        terminal.innerHTML = '<div class="log-line" style="color:#8b949e">// Console cleared by user<br><br></div>';
    }

    function addLogLine() {
        if(isPaused) return;

        // --- MOCK DATA (Sostituire con AJAX in produzione) ---
        const ips = ['192.168.1.45', '10.0.0.12', '45.33.22.11', '89.11.23.4', '172.16.0.50'];
        // Trucco: se scrivi un IP nel filtro, lo facciamo apparire ogni tanto per testare
        const searchIp = filterIpInput.value.trim();
        if(searchIp !== '' && Math.random() > 0.7) ips.push(searchIp);

        const attacks = ['SQL Injection', 'XSS Attempt', 'Bad User Agent', 'Directory Traversal'];
        const methods = ['GET', 'POST', 'PUT'];
        const urls = ['/login.php', '/admin/config', '/wp-content/uploads', '/api/v1/data', '/etc/shadow'];
        
        const ip = ips[Math.floor(Math.random() * ips.length)];
        const atk = attacks[Math.floor(Math.random() * attacks.length)];
        const method = methods[Math.floor(Math.random() * methods.length)];
        const url = urls[Math.floor(Math.random() * urls.length)];
        
        // --- FILTRO LATO CLIENT (Immediato) ---
        const searchType = filterTypeInput.value;

        // Se l'input non è vuoto e l'IP non lo contiene -> Salta
        if(searchIp !== '' && !ip.includes(searchIp)) return;
        // Se il tipo è selezionato e non corrisponde -> Salta
        if(searchType !== '' && atk !== searchType) return;

        // Formattazione
        const now = new Date().toLocaleTimeString('it-IT');
        let color = '#3fb950'; // Verde default
        let riskStyle = '';
        
        if(atk === 'SQL Injection') { color = '#f85149'; riskStyle='font-weight:bold; text-shadow:0 0 5px rgba(248,81,73,0.4);'; }
        if(atk === 'XSS Attempt') { color = '#d29922'; }
        if(atk === 'Directory Traversal') { color = '#a371f7'; }

        const line = `
            <div class="log-line" style="margin-bottom: 4px; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom:4px; display:flex; gap:15px; align-items:center;">
                <span style="color:#8b949e; min-width:70px;">${now}</span> 
                <span style="color:#79c0ff; min-width:110px;">${ip}</span> 
                <span style="color:#fff; min-width:40px; font-weight:bold;">${method}</span> 
                <span style="color:${color}; min-width:160px; ${riskStyle}">${atk}</span> 
                <span style="color:#8b949e; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">${url}</span>
            </div>`;

        terminal.insertAdjacentHTML('afterbegin', line); 
        
        // Performance: mantieni max 100 righe
        if(terminal.children.length > 100) terminal.lastElementChild.remove();
    }

    // Loop di generazione
    setInterval(() => {
        // Randomizza frequenza
        if(Math.random() > 0.3) addLogLine();
    }, 800);
</script>

<?php require_once '../footer.php'; ?>