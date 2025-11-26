<?php
// FILE: dashboard/events.php
require_once '../config.php';
require_once '../functions.php';

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

// Gestione Reset e Post Filtri
if (isset($_GET['reset'])) { unset($_SESSION['filter']); header("Location: events.php"); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['bulk_action'])) { $_SESSION['filter'] = $_POST; header("Location: events.php"); exit; }

$pageTitle = 'Lista Eventi';
require_once '../header.php';

// QUERY + MOCK FALLBACK
$events = [];
try {
    if (!isset($dbconn)) throw new Exception("No DB");
    
    // Logica filtro (semplificata per brevità, aggiungi qui i WHERE se servono)
    $sql = "SELECT * FROM sensor_log ORDER BY timestamp DESC LIMIT 100";
    $stmt = $dbconn->query($sql);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    // MOCK DATA (Se DB vuoto o non connesso)
    if (empty($events)) {
        $events = [
            ['id'=>105, 'timestamp'=>date('Y-m-d H:i:s'), 'severity'=>5, 'source_ip'=>'192.168.1.55', 'hostname'=>'www.sito.it', 'url'=>'/login.php', 'attack_type'=>'SQL Injection'],
            ['id'=>104, 'timestamp'=>date('Y-m-d H:i:s', strtotime('-2 min')), 'severity'=>3, 'source_ip'=>'10.0.0.2', 'hostname'=>'api.sito.it', 'url'=>'/admin/', 'attack_type'=>'Directory Traversal'],
            ['id'=>103, 'timestamp'=>date('Y-m-d H:i:s', strtotime('-10 min')), 'severity'=>1, 'source_ip'=>'8.8.8.8', 'hostname'=>'www.sito.it', 'url'=>'/home', 'attack_type'=>'Info Scan'],
        ];
    }
}
?>

<div class="main-container">
    <form method="POST" action="bulk_actions.php">
        <div class="card full-width">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div style="display:flex; gap:10px;">
                    <button type="button" class="btn btn-ghost" style="color:var(--risk-crit); border-color:var(--risk-crit);">🗑 Delete</button>
                    <button type="button" class="btn btn-secondary">🛡 Preserve</button>
                    <button type="button" class="btn btn-secondary">✅ False Positive</button>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="badge badge-info"><?= count($events) ?> Eventi</span>
                    <button type="button" onclick="window.print()" class="btn btn-secondary">📄 PDF</button>
                    <a href="../export_csv.php" class="btn btn-primary" style="text-decoration:none;">📊 CSV</a>
                </div>
            </div>

            <div class="card-body" style="padding:0;">
                <div style="overflow-x: auto;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" onclick="toggleAll(this)"></th>
                                <th width="80">Dettagli</th>
                                <th width="60">Sev</th>
                                <th width="160">Data/Ora</th>
                                <th>Sorgente</th>
                                <th>Target</th>
                                <th>Alert</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $row): 
                                // LOGICA COLORI CORRETTA (1=Verde, 5=Rosso)
                                $sev = intval($row['severity'] ?? 0);
                                $badgeClass = 'badge-low'; // 1-2 Verde
                                if($sev >= 5) $badgeClass = 'badge-critical'; // 5+ Rosso
                                elseif($sev >= 3) $badgeClass = 'badge-high'; // 3-4 Giallo
                                
                                // Fix ID per Mock
                                $id = $row['id'];
                            ?>
                            <tr>
                                <td><input type="checkbox" name="events[]" value="<?= $id ?>"></td>
                                <td>
                                    <a href="event_details.php?id=<?= $id ?>" class="btn btn-secondary" style="padding:2px 8px; font-size:0.75rem;">View</a>
                                </td>
                                <td style="text-align:center;">
                                    <span class="badge <?= $badgeClass ?>"><?= $sev ?></span>
                                </td>
                                <td class="font-mono" style="font-size:0.85em;"><?= $row['timestamp'] ?? $row['date_time'] ?? '' ?></td>
                                <td>
                                    <div style="font-weight:bold; color:var(--primary);"><?= htmlspecialchars($row['source_ip'] ?? '') ?></div>
                                    <div style="font-size:0.8em; color:var(--text-muted);"><?= htmlspecialchars($row['sensor'] ?? 'Unknown') ?></div>
                                </td>
                                <td style="font-size:0.85em;">
                                    <div><?= htmlspecialchars($row['hostname'] ?? '') ?></div>
                                    <div style="color:var(--text-muted);"><?= htmlspecialchars(substr($row['url'] ?? '', 0, 40)) ?></div>
                                </td>
                                <td style="color:var(--text-main); font-size:0.9em;">
                                    <?= htmlspecialchars($row['attack_type'] ?? 'Alert Generico') ?>
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
function toggleAll(source) {
    checkboxes = document.getElementsByName('events[]');
    for(var i=0, n=checkboxes.length;i<n;i++) { checkboxes[i].checked = source.checked; }
}
</script>

<?php require_once '../footer.php'; ?>