<?php
// Dashboard mock-only: non usa DB, solo dati finti
$title = 'Dashboard (Mock)';
require_once __DIR__ . '/../header.php';

// Seed giornaliero per avere numeri stabili nella giornata
mt_srand((int)date('Ymd'));

// KPI finte
$totalEvents = mt_rand(5000, 20000);
$userCount   = mt_rand(5, 80);
$recentCount = mt_rand(10, 40);

// Generatori finti
function mock_ip(){ return mt_rand(1,255).'.'.mt_rand(0,255).'.'.mt_rand(0,255).'.'.mt_rand(1,254); }
function mock_host(){
  $doms = ['example.com','corp.local','intra.net','svc.local','edge.net'];
  $subs = ['api','app','www','gw','proxy','waf','auth','cdn'];
  return $subs[array_rand($subs)].'.'.$doms[array_rand($doms)];
}
function mock_severity(){
  $levels = ['info','low','medium','high','critical'];
  return $levels[array_rand($levels)];
}
function mock_info(){
  $msgs = [
    'SQLi pattern detected',
    'XSS attempt blocked',
    'Bad user agent',
    'Rate limit exceeded',
    'Suspicious request',
    'Known scanner signature',
  ];
  return $msgs[array_rand($msgs)];
}

$rows = [];
$N = $recentCount;
for($i=0; $i<$N; $i++){
  $ts = time() - mt_rand(0, 72*3600); // ultime 72 ore
  $rows[] = [
    'id'       => mt_rand(1000, 9999),
    'date'     => date('Y-m-d H:i:s', $ts),
    'severity' => mock_severity(),
    'ip'       => mock_ip(),
    'host'     => mock_host(),
    'info'     => mock_info(),
  ];
}
if (!function_exists('severity_class')) {
    function severity_class(string $sev): string {
        $s = strtolower(trim($sev));
        $map = [
            '0'        => 'info',
            'info'     => 'info',
            'debug'    => 'info',
            'low'      => 'low',
            '1'        => 'low',
            'medium'   => 'medium',
            '2'        => 'medium',
            'med'      => 'medium',
            'high'     => 'high',
            '3'        => 'high',
            'critical' => 'critical',
            'crit'     => 'critical',
            '4'        => 'critical',
            '5'        => 'critical',
        ];
        $norm = $map[$s] ?? 'info';
        return 'badge sev-' . $norm;
    }
}

?>
<section class="grid cols-3">
  <div class="card">
    <h2>Eventi totali</h2>
    <div class="metric">
      <span class="value"><?= $totalEvents ?></span>
      <span class="tag">all time</span>
    </div>
    <canvas data-mock></canvas>
  </div>

  <div class="card">
    <h2>Utenti</h2>
    <div class="metric">
      <span class="value"><?= $userCount ?></span>
      <span class="tag">utenti monitorati</span>
    </div>
    <canvas data-mock></canvas>
  </div>

  <div class="card">
    <h2>Eventi recenti</h2>
    <div class="metric">
      <span class="value"><?= count($rows) ?></span>
      <span class="tag">ultime 72h (mock)</span>
    </div>
    <canvas data-mock></canvas>
  </div>
</section>

<section class="card" style="margin-top:16px">
  <h2>Eventi recenti (mock)</h2>

  <div class="kpi">
    <span class="chip">Tot eventi: <?= count($rows) ?></span>
    <span class="chip">Mock attivo</span>
  </div>

  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Data</th>
        <th>Gravità</th>
        <th>IP / Host</th>
        <th>Dettagli</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($rows as $event): ?>
        <tr>
          <td><?= htmlspecialchars($event['id']) ?></td>
          <td><?= htmlspecialchars($event['date']) ?></td>
          <td>
  <span class="<?= severity_class($event['severity']) ?>">
    <?= htmlspecialchars($event['severity']) ?>
  </span>
</td>
          <td>
            <?= htmlspecialchars($event['ip']) ?><br>
            <span class="muted"><?= htmlspecialchars($event['host']) ?></span>
          </td>
          <td><?= htmlspecialchars($event['info']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <p class="alert" style="margin-top:12px">
    Questa pagina usa solo dati generati al volo, senza DB. Serve per testare il nuovo frontend.
  </p>
</section>

<?php require_once __DIR__ . '/../footer.php'; ?>
