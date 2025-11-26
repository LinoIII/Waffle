<?php
// FILE: dashboard/event_details.php
require_once '../config.php';
require_once '../functions.php';

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$pageTitle = 'Dettaglio Evento';
require_once '../header.php';

$eventId = $_GET['id'] ?? 0;
$event = null;

// 1. PROVA DB REALE
try {
    if(isset($dbconn)) {
        $stmt = $dbconn->prepare("SELECT * FROM sensor_log WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $eventId);
        $stmt->execute();
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) {}

// 2. FALLBACK MOCK (Se DB fallisce o evento mock cliccato)
if (!$event) {
    // Creo un evento finto per far vedere la grafica
    $event = [
        'id' => $eventId,
        'timestamp' => date('Y-m-d H:i:s'),
        'sensor' => 'Apache-Front-Mock',
        'source_ip' => '192.168.1.55',
        'hostname' => 'www.miosito.it',
        'url' => '/login.php?user=\' OR 1=1',
        'http_method' => 'POST',
        'user_agent' => 'Mozilla/5.0 (compatible; EvilBot/1.0)',
        'action' => 'BLOCKED',
        'rule_id' => '981173',
        'severity' => '5',
        'attack_type' => 'SQL Injection',
        'full_log' => "ModSecurity: Access denied with code 403 (phase 2). \nPattern match \"' OR 1=1\" at ARGS:user. \n[file \"/etc/modsecurity/rules.conf\"] [line \"45\"] [id \"981173\"] \n[msg \"SQL Injection Attack Detected\"] [severity \"CRITICAL\"]"
    ];
}
?>

<div class="main-container">
    <div style="margin-bottom: 20px;">
        <a href="events.php" class="btn btn-secondary">← Torna alla lista</a>
    </div>

    <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
        
        <div class="card">
            <div class="card-header">Informazioni Richiesta</div>
            <div class="card-body">
                <table class="data-table">
                    <tr><th width="150">ID Evento</th><td>#<?= $event['id'] ?></td></tr>
                    <tr><th>Timestamp</th><td><?= $event['timestamp'] ?></td></tr>
                    <tr><th>Sensore</th><td><?= htmlspecialchars($event['sensor']) ?></td></tr>
                    <tr><th>IP Sorgente</th><td>
                        <?= htmlspecialchars($event['source_ip']) ?>
                        <a href="https://www.abuseipdb.com/check/<?= $event['source_ip'] ?>" target="_blank" class="badge badge-info" style="margin-left:10px; text-decoration:none;">Check Reputation</a>
                    </td></tr>
                    <tr><th>Target</th><td><?= htmlspecialchars($event['hostname']) ?></td></tr>
                    <tr><th>URL</th><td style="word-break:break-all; color:var(--primary); font-family:monospace;"><?= htmlspecialchars($event['url']) ?></td></tr>
                    <tr><th>User Agent</th><td style="font-size:0.85em; color:var(--text-muted);"><?= htmlspecialchars($event['user_agent']) ?></td></tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Analisi WAF</div>
            <div class="card-body">
                <div style="margin-bottom:15px;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8em;">AZIONE</label>
                    <span class="badge badge-critical" style="font-size:1rem;"><?= htmlspecialchars($event['action']) ?></span>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8em;">RULE ID</label>
                    <span style="font-family:monospace; font-size:1.1rem;"><?= htmlspecialchars($event['rule_id']) ?></span>
                </div>
                <div style="margin-bottom:15px;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8em;">SEVERITY</label>
                    <span class="badge badge-high"><?= htmlspecialchars($event['severity']) ?> (Critical)</span>
                </div>
                <hr style="border:0; border-top:1px solid var(--border);">
                <div style="display:flex; flex-direction:column; gap:10px;">
                    <button class="btn btn-secondary" style="width:100%;">Mark False Positive</button>
                    <button class="btn btn-ghost" style="width:100%;">Delete Event</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card full-width">
        <div class="card-header">Raw Log ModSecurity</div>
        <div class="card-body" style="background:#0d1117; color:#c9d1d9; font-family:'JetBrains Mono', monospace; font-size:0.85rem; white-space:pre-wrap; overflow-x:auto;">
<?= htmlspecialchars($event['full_log']) ?>
        </div>
    </div>
</div>

<?php require_once '../footer.php'; ?>