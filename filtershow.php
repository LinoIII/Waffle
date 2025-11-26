<?php
// FILE: filtershow.php (ROOT)
$title = 'Risultati Analisi';
require_once __DIR__ . '/header.php';

// ... (Parte PHP di recupero filtri invariata - OMETTO PER BREVITÀ, MANTIENI LA TUA LOGICA DI GET) ...
// Mock se non ci sono dati per visualizzare l'esempio
if (!isset($results) || empty($results)) {
    // Dati finti per farti vedere la grafica
    $results = [
        ['id'=>101, 'date'=>'2025-11-26 11:18:49', 'severity'=>'CRITICAL', 'ip'=>'192.159.99.101', 'info'=>'Host header is numeric IP', 'host'=>'81.23.81.134'],
        ['id'=>102, 'date'=>'2025-11-26 11:12:51', 'severity'=>'HIGH', 'ip'=>'193.34.213.150', 'info'=>'SQL Injection Detected', 'host'=>'81.23.87.78'],
    ];
}
?>

<div class="main-container">

    <form method="POST" action="bulk_actions.php" id="bulkForm">
        <div class="card full-width">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-ghost" title="Elimina selezionati">🗑 Delete</button>
                    <button type="button" class="btn btn-secondary" title="Preserva eventi">🛡 Preserve</button>
                    <button type="button" class="btn btn-secondary" title="Segna come Falso Positivo">✅ Mark False Positive</button>
                </div>
                <span class="badge badge-info"><?= count($results) ?> Eventi</span>
            </div>

            <div class="card-body" style="padding:0;">
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" onclick="toggleAll(this)"></th>
                                <th width="80">Azione</th> <th width="50">Sev.</th>
                                <th width="150">Data/Ora</th>
                                <th>Sensore/IP</th>
                                <th>Hostname/Path</th>
                                <th>Alert Regola</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $row): 
                                // Mapping colori
                                $sev = strtoupper($row['severity'] ?? 'INFO');
                                $badgeClass = 'badge-info';
                                if($sev=='CRITICAL') $badgeClass='badge-critical';
                                if($sev=='HIGH') $badgeClass='badge-high';
                                if($sev=='WARNING') $badgeClass='badge-medium';
                            ?>
                            <tr>
                                <td><input type="checkbox" name="events[]" value="<?= $row['id'] ?>"></td>
                                
                                <td>
                                    <a href="dashboard/event_details.php?id=<?= $row['id'] ?>" class="btn btn-secondary" style="padding:2px 8px; font-size:0.75rem;">Details</a>
                                </td>

                                <td style="text-align:center;">
                                    <span class="badge <?= $badgeClass ?>" title="<?= $sev ?>">⚡</span>
                                </td>

                                <td style="font-size:0.85em; white-space:nowrap;">
                                    <?= htmlspecialchars($row['date']) ?>
                                </td>

                                <td>
                                    <div style="font-weight:bold; color:var(--primary);"><?= htmlspecialchars($row['ip']) ?></div>
                                    <div style="font-size:0.8em; color:var(--text-muted);">Sensor: Alnitak</div> </td>

                                <td style="font-size:0.85em;">
                                    <div>Host: <?= htmlspecialchars($row['host']) ?></div>
                                    <div style="color:var(--text-muted);">/index.asp</div> </td>

                                <td style="color:var(--risk-crit); font-weight:500;">
                                    <?= htmlspecialchars($row['info']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Script per selezionare tutte le checkbox
function toggleAll(source) {
    checkboxes = document.getElementsByName('events[]');
    for(var i=0, n=checkboxes.length;i<n;i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>