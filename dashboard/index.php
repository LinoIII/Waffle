<?php
$title = 'Dashboard Operativa';
require_once __DIR__ . '/../header.php';

// --- LOGICA BACKEND SIMULATA PER UI ---
// Se le variabili non arrivano dal controller, usiamo valori di default sicuri (0 o array vuoti)
$totalEvents = $totalEvents ?? 0;
$userCount = $userCount ?? 0;
$recentEvents = isset($recentEvents) && is_array($recentEvents) ? $recentEvents : [];

// Helper per mappare la gravità numerica a classi CSS
function getSeverityBadge($level) {
    // Normalizza input
    $lvl = strtolower((string)$level);
    
    // Mappa livelli vecchi e nuovi
    if (in_array($lvl, ['critical', '5', '4'])) return ['badge-critical', 'CRITICAL'];
    if (in_array($lvl, ['high', '3'])) return ['badge-high', 'HIGH'];
    if (in_array($lvl, ['medium', 'med', '2'])) return ['badge-medium', 'MEDIUM'];
    if (in_array($lvl, ['low', '1'])) return ['badge-low', 'LOW'];
    
    return ['badge-info', 'INFO'];
}

// MOCK DATA: Se non ci sono eventi, mostriamo dati finti per vedere il design
if (empty($recentEvents) && $totalEvents == 0) {
    $recentEvents = [
        ['id'=>104, 'date'=>date('Y-m-d H:i:s'), 'ip'=>'192.168.1.45', 'host'=>'crm.local', 'severity'=>'critical', 'info'=>'SQL Injection attempt in login'],
        ['id'=>103, 'date'=>date('Y-m-d H:i:s', strtotime('-10 min')), 'ip'=>'10.0.0.12', 'host'=>'wiki.local', 'severity'=>'medium', 'info'=>'XSS payload detected'],
        ['id'=>102, 'date'=>date('Y-m-d H:i:s', strtotime('-1 hour')), 'ip'=>'45.12.33.11', 'host'=>'app.public', 'severity'=>'low', 'info'=>'Path traversal probing'],
    ];
    $totalEvents = 15420;
    $userCount = 12;
}
?>

<section class="grid cols-3" style="margin-bottom: 24px;">
  <div class="card">
    <div class="metric">
      <span class="value"><?= number_format($totalEvents) ?></span>
    </div>
    <div class="metric label">Eventi Rilevati (Total)</div>
    <canvas data-mock></canvas>
  </div>

  <div class="card">
    <div class="metric">
      <span class="value" style="color: var(--warn)"><?= number_format($userCount) ?></span>
    </div>
    <div class="metric label">Sensori / Utenti Attivi</div>
    <canvas data-mock></canvas>
  </div>

  <div class="card">
    <div class="metric">
      <span class="value" style="color: var(--ok)">Online</span>
    </div>
    <div class="metric label">Stato Sistema</div>
    <div style="font-size:11px; color:var(--muted); margin-top:5px;">Uptime: 14d 2h 10m</div>
  </div>
</section>

<section class="card">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
      <h2 style="margin:0; font-size:16px;">🚨 Ultimi Eventi Rilevati</h2>
      <a href="../filter.php" class="btn btn-ghost" style="padding:4px 10px; font-size:11px;">Vedi Tutti</a>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th width="80">ID</th>
          <th width="160">Timestamp</th>
          <th width="100">Severity</th>
          <th width="180">Source IP</th>
          <th>Dettaglio Evento</th>
          <th width="80" style="text-align:right">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($recentEvents as $ev): 
            list($badgeClass, $badgeLabel) = getSeverityBadge($ev['severity'] ?? 'info');
        ?>
          <tr>
            <td style="color:var(--muted)">#<?= htmlspecialchars($ev['id'] ?? '-') ?></td>
            <td><?= htmlspecialchars($ev['date'] ?? '-') ?></td>
            <td>
                <span class="badge <?= $badgeClass ?>"><?= $badgeLabel ?></span>
            </td>
            <td style="font-family:var(--mono); letter-spacing:-0.5px;">
                <?= htmlspecialchars($ev['ip'] ?? '0.0.0.0') ?>
                <?php if(!empty($ev['host'])): ?>
                    <div style="font-size:10px; color:var(--muted)"><?= htmlspecialchars($ev['host']) ?></div>
                <?php endif; ?>
            </td>
            <td>
                <?= htmlspecialchars(substr($ev['info'] ?? 'No detail', 0, 80)) ?>...
            </td>
            <td style="text-align:right">
                <a href="../filtershow.php?id=<?= $ev['id'] ?? 0 ?>" class="btn-ghost" style="padding:2px 6px; border-radius:4px; font-size:10px;">VIEW</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require_once __DIR__ . '/../footer.php'; ?>