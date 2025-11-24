<?php
// Pagina filtro eventi
$title = 'Filtro eventi';
require_once __DIR__ . '/header.php';

// Leggo eventuali valori già passati (per mantenere i campi compilati)
$q       = $_GET['q']       ?? '';
$ip      = $_GET['ip']      ?? '';
$host    = $_GET['host']    ?? '';
$from    = $_GET['from']    ?? '';
$to      = $_GET['to']      ?? '';
$severity= $_GET['severity']?? '';
?>
<section class="card">
  <h2>Filtra eventi</h2>
  <p class="muted" style="margin-bottom:10px;">
    Imposta uno o più criteri per restringere gli eventi mostrati. Tutti i campi sono opzionali.
  </p>

  <form method="get" action="filtershow.php" class="form-grid" autocomplete="off">
    <div class="form-row">
      <label for="q">Ricerca libera</label>
      <input
        type="text"
        id="q"
        name="q"
        value="<?= htmlspecialchars($q) ?>"
        placeholder="ID evento, messaggio, rule, ecc."
      >
    </div>

    <div class="form-grid cols-2">
      <div class="form-row">
        <label for="ip">IP sorgente</label>
        <input
          type="text"
          id="ip"
          name="ip"
          value="<?= htmlspecialchars($ip) ?>"
          placeholder="es. 192.168.0.10 o CIDR"
        >
      </div>

      <div class="form-row">
        <label for="host">Host</label>
        <input
          type="text"
          id="host"
          name="host"
          value="<?= htmlspecialchars($host) ?>"
          placeholder="es. app.example.com"
        >
      </div>
    </div>

    <div class="form-grid cols-2">
      <div class="form-row">
        <label for="from">Da data</label>
        <input
          type="date"
          id="from"
          name="from"
          value="<?= htmlspecialchars($from) ?>"
        >
      </div>

      <div class="form-row">
        <label for="to">A data</label>
        <input
          type="date"
          id="to"
          name="to"
          value="<?= htmlspecialchars($to) ?>"
        >
      </div>
    </div>

    <div class="form-row">
      <label for="severity">Gravità minima</label>
      <select id="severity" name="severity">
        <option value="">Qualsiasi</option>
        <option value="info"     <?= $severity==='info'     ? 'selected' : '' ?>>Info</option>
        <option value="low"      <?= $severity==='low'      ? 'selected' : '' ?>>Low</option>
        <option value="medium"   <?= $severity==='medium'   ? 'selected' : '' ?>>Medium</option>
        <option value="high"     <?= $severity==='high'     ? 'selected' : '' ?>>High</option>
        <option value="critical" <?= $severity==='critical' ? 'selected' : '' ?>>Critical</option>
      </select>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn">Applica filtro</button>
      <a href="filter.php" class="btn" style="background:transparent;border:1px solid rgba(148,163,184,.6);color:var(--muted);">
        Reset
      </a>
    </div>
  </form>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
