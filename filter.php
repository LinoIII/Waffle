<?php
$title = 'Filtro Eventi WAF';
require_once __DIR__ . '/header.php';

// Inizializzazione variabili sicura
$q = $_GET['q'] ?? '';
$ip = $_GET['ip'] ?? '';
$host = $_GET['host'] ?? '';
$from = $_GET['from'] ?? '';
$to = $_GET['to'] ?? '';
$severity = $_GET['severity'] ?? '';
?>

<section class="card" style="max-width: 800px; margin: 0 auto;">
  <div style="border-bottom:1px solid var(--border); padding-bottom:15px; margin-bottom:20px;">
      <h2 style="margin:0;">🔍 Analisi Eventi</h2>
      <p style="color:var(--muted); font-size:13px; margin:5px 0 0 0;">Definisci i criteri per filtrare i log del WAF.</p>
  </div>

  <form method="get" action="filtershow.php" class="form-grid" autocomplete="off">
    
    <div class="form-row">
      <label for="q">Ricerca Payload / ID</label>
      <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Es. 'SELECT', 'UNION', o ID evento...">
    </div>

    <div class="grid cols-3" style="grid-template-columns: 1fr 1fr; gap: 16px;">
      <div class="form-row">
        <label for="ip">IP Sorgente</label>
        <input type="text" id="ip" name="ip" value="<?= htmlspecialchars($ip) ?>" placeholder="192.168.x.x">
      </div>

      <div class="form-row">
        <label for="host">Host Colpito</label>
        <input type="text" id="host" name="host" value="<?= htmlspecialchars($host) ?>" placeholder="www.azienda.local">
      </div>
    </div>

    <div class="grid cols-3" style="grid-template-columns: 1fr 1fr; gap: 16px;">
      <div class="form-row">
        <label for="from">Da Data</label>
        <input type="date" id="from" name="from" value="<?= htmlspecialchars($from) ?>">
      </div>

      <div class="form-row">
        <label for="to">A Data</label>
        <input type="date" id="to" name="to" value="<?= htmlspecialchars($to) ?>">
      </div>
    </div>

    <div class="form-row">
      <label for="severity">Gravità Minima</label>
      <select id="severity" name="severity">
        <option value="">Tutte</option>
        <option value="info" <?= $severity==='info' ? 'selected' : '' ?>>Info (Log)</option>
        <option value="low" <?= $severity==='low' ? 'selected' : '' ?>>Low (Warning)</option>
        <option value="medium" <?= $severity==='medium' ? 'selected' : '' ?>>Medium (Suspicious)</option>
        <option value="high" <?= $severity==='high' ? 'selected' : '' ?>>High (Attack)</option>
        <option value="critical" <?= $severity==='critical' ? 'selected' : '' ?>>Critical (Breach)</option>
      </select>
    </div>

    <div style="display:flex; gap:10px; margin-top:10px;">
      <button type="submit" class="btn">Applica Filtri</button>
      <a href="filter.php" class="btn btn-ghost">Reset</a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>