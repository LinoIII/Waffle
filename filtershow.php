<?php
$title = 'Risultati filtro';
require_once __DIR__ . '/header.php';

// Parametri filtro (solo per mostrarli nella UI; la logica vera sta lato backend)
$q        = $_GET['q']        ?? '';
$ip       = $_GET['ip']       ?? '';
$host     = $_GET['host']     ?? '';
$from     = $_GET['from']     ?? '';
$to       = $_GET['to']       ?? '';
$severity = $_GET['severity'] ?? '';

/*
 * Lato backend, in base alla versione che userai, ci sarà una variabile
 * con l'elenco eventi filtrati (es. $results, $events, $filter_results).
 * Qui mettiamo un fallback generico per non far uscire warning.
 */
$results = $results
           ?? ($filter_results ?? ($events ?? []));
if (!is_array($results)) {
    $results = [];
}

// Helper per colorare la severità se in futuro la useremo
function severity_class(string $sev): string {
    $s = strtolower(trim($sev));

    // mappa anche eventuali valori numerici o testuali diversi
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

    $norm = $map[$s] ?? 'info'; // default info se non riconosciuto

    return 'badge sev-' . $norm;
}

?>
<section class="card">
  <h2>Risultati filtro</h2>

  <div class="kpi">
    <span class="chip">
      <?= count($results) ?> risultato<?= count($results) === 1 ? '' : 'i' ?>
    </span>

    <?php if($q !== ''): ?>
      <span class="chip">Query: <?= htmlspecialchars($q) ?></span>
    <?php endif; ?>
    <?php if($ip !== ''): ?>
      <span class="chip">IP: <?= htmlspecialchars($ip) ?></span>
    <?php endif; ?>
    <?php if($host !== ''): ?>
      <span class="chip">Host: <?= htmlspecialchars($host) ?></span>
    <?php endif; ?>
    <?php if($from !== '' || $to !== ''): ?>
      <span class="chip">
        Range:
        <?= htmlspecialchars($from ?: '—') ?> → <?= htmlspecialchars($to ?: '—') ?>
      </span>
    <?php endif; ?>
    <?php if($severity !== ''): ?>
      <span class="chip">Sev ≥ <?= htmlspecialchars(ucfirst($severity)) ?></span>
    <?php endif; ?>
  </div>

  <?php if (empty($results)): ?>

    <p class="alert">
      Nessun evento trovato con i criteri indicati.
      Prova ad allargare il range temporale o rimuovere qualche filtro.
    </p>

  <?php else: ?>

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
        <?php foreach ($results as $row): ?>
          <?php
            $id       = $row['id']       ?? ($row['event_id'] ?? '');
            $date     = $row['date']     ?? ($row['time'] ?? '');
            $sev      = $row['severity'] ?? ($row['level'] ?? '');
            $rowIp    = $row['ip']       ?? ($row['src_ip'] ?? '');
            $rowHost  = $row['host']     ?? ($row['hostname'] ?? '');
            $info     = $row['info']     ?? ($row['msg'] ?? '');
          ?>
          <tr>
            <td><?= htmlspecialchars($id) ?></td>
            <td><?= htmlspecialchars($date) ?></td>
            <td>
              <span class="<?= severity_class($sev) ?>">
                <?= htmlspecialchars($sev) ?>
              </span>
            </td>
            <td>
              <?= htmlspecialchars($rowIp) ?>
              <?php if($rowHost !== ''): ?>
                <br><span class="muted"><?= htmlspecialchars($rowHost) ?></span>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($info) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php endif; ?>

  <div class="form-actions" style="margin-top:14px;">
    <a href="filter.php" class="btn">Modifica filtro</a>
  </div>
</section>

<?php require_once __DIR__ . '/footer.php'; ?>
