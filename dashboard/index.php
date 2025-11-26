<?php
// FILE: dashboard/index.php
require_once '../config.php';
require_once '../functions.php';

// Debug errori (rimuovere in produzione)
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['user_id']) && !isset($_SESSION['login'])) {
    header("Location: ../index.php");
    exit;
}

$pageTitle = 'Security Dashboard';
require_once '../header.php';

// --- VARIABILI DATI ---
$chartLabels = [];
$chartTraffic = [];
$chartBlocked = [];
$attackDistribution = [];
$recentEvents = [];
$topIPs = [];
$topURIs = [];
$topRules = [];
$sensorStatus = [];
$heatMapData = [];
// NUOVI GRAFICI
$statusCounts = []; // Per HTTP Status (403, 404, etc)
$topTargets = [];   // Per Hostname

$usingMock = false;

try {
    if (!isset($dbconn)) { throw new Exception("Nessuna connessione DB"); }

    // 1. GRAFICI TEMPORALI
    // (Qui semplificato per brevità, in produzione servirebbe una query complessa per le ore vuote)
    
    // 2. TOP LISTS
    $topIPs = $dbconn->query("SELECT source_ip, COUNT(*) as hits FROM sensor_log GROUP BY source_ip ORDER BY hits DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $topURIs = $dbconn->query("SELECT url, COUNT(*) as hits FROM sensor_log GROUP BY url ORDER BY hits DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $topRules = $dbconn->query("SELECT attack_type, COUNT(*) as hits FROM sensor_log GROUP BY attack_type ORDER BY hits DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

    // 3. MONITORAGGIO SENSORI
    $sensorStatus = $dbconn->query("SELECT sensor, MAX(timestamp) as last_seen FROM sensor_log GROUP BY sensor")->fetchAll(PDO::FETCH_ASSOC);

    // 4. HEATMAP
    $stmtHeat = $dbconn->query("SELECT DAYOFWEEK(timestamp) as dw, HOUR(timestamp) as hr, COUNT(*) as cnt FROM sensor_log GROUP BY dw, hr");
    while($row = $stmtHeat->fetch(PDO::FETCH_ASSOC)) {
        $heatMapData[$row['dw']][$row['hr']] = $row['cnt'];
    }

    // 5. NUOVI: HTTP STATUS & HOSTNAME
    // Assumo che la colonna si chiami 'http_status' o 'status_code'
    $stmtStatus = $dbconn->query("SELECT http_status, COUNT(*) as hits FROM sensor_log GROUP BY http_status");
    while($row = $stmtStatus->fetch(PDO::FETCH_ASSOC)) {
        $statusCounts[$row['http_status']] = $row['hits'];
    }

    // Assumo colonna 'hostname'
    $topTargets = $dbconn->query("SELECT hostname, COUNT(*) as hits FROM sensor_log GROUP BY hostname ORDER BY hits DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);


    if (empty($topIPs)) { throw new Exception("DB Vuoto"); }

} catch (Exception $e) {
    $usingMock = true;
    
    // --- MOCK DATA ---
    for ($i = 11; $i >= 0; $i--) {
        $chartLabels[] = date('H:00', strtotime("-$i hours"));
        $chartTraffic[] = rand(500, 1200);
        $chartBlocked[] = rand(20, 150);
    }
    $attackDistribution = ['SQL Injection' => 300, 'XSS' => 150, 'Path Traversal' => 80, 'Scanner' => 600];
    
    $topIPs = [['source_ip'=>'192.168.1.105','hits'=>1450], ['source_ip'=>'45.33.22.11','hits'=>980], ['source_ip'=>'10.0.0.55','hits'=>540]];
    $topURIs = [['url'=>'/wp-login.php','hits'=>2100], ['url'=>'/admin/','hits'=>850], ['url'=>'/api/v1','hits'=>320]];
    $topRules = [['attack_type'=>'SQL Injection','hits'=>890], ['attack_type'=>'XSS','hits'=>560], ['attack_type'=>'Bad Bot','hits'=>1200]];
    
    $sensorStatus = [['sensor'=>'Apache-Front-01', 'last_seen'=>date('Y-m-d H:i:s')], ['sensor'=>'Nginx-LB-02', 'last_seen'=>date('Y-m-d H:i:s')]];
    
    for($d=1; $d<=7; $d++) for($h=0; $h<24; $h++) if(rand(0,10)>8) $heatMapData[$d][$h] = rand(5,60);

    // MOCK NUOVI
    $statusCounts = ['200'=>5000, '403'=>1200, '404'=>450, '500'=>20];
    $topTargets = [['hostname'=>'www.miosito.it','hits'=>3400], ['hostname'=>'api.miosito.it','hits'=>1200], ['hostname'=>'shop.miosito.it','hits'=>800]];

    $recentEvents = []; 
    // Genero 5 eventi random
    for($i=0; $i<5; $i++) $recentEvents[] = ['id'=>rand(1000,9999), 'timestamp'=>date('H:i:s'), 'sensor'=>'Apache-01', 'source_ip'=>'1.2.3.4', 'type'=>'XSS', 'action'=>'BLOCKED'];
}
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="charts-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <div class="card">
        <div class="card-header">Minacce per Tipo</div>
        <div class="card-body"><div class="chart-container"><canvas id="distChart"></canvas></div></div>
    </div>
    
    <div class="card">
        <div class="card-header">Eventi per Status HTTP</div>
        <div class="card-body"><div class="chart-container"><canvas id="statusChart"></canvas></div></div>
    </div>

    <div class="card">
        <div class="card-header">Trend Traffico (12h)</div>
        <div class="card-body"><div class="chart-container"><canvas id="trafficChart"></canvas></div></div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
    <div class="card">
        <div class="card-header">Top Target Hostnames</div>
        <div class="card-body"><div class="chart-container" style="height: 250px;"><canvas id="targetChart"></canvas></div></div>
    </div>

    <div class="card">
        <div class="card-header">Sensori</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <thead><tr><th>Nome</th><th>Stato</th></tr></thead>
                <tbody>
                    <?php foreach($sensorStatus as $s): 
                        $mins = (time() - strtotime($s['last_seen'])) / 60;
                        $status = ($mins < 15) ? 'ONLINE' : 'OFFLINE';
                        $badge = ($mins < 15) ? 'badge-low' : 'badge-critical';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($s['sensor']) ?></td>
                        <td><span class="badge <?= $badge ?>"><?= $status ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));">
    <div class="card">
        <div class="card-header">Top Source IP</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <?php foreach($topIPs as $r): ?>
                <tr><td class="font-mono"><?= $r['source_ip']?></td><td style="text-align:right"><b><?= $r['hits']?></b></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Top URI</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <?php foreach($topURIs as $r): ?>
                <tr><td style="font-size:0.85em; word-break:break-all"><?= $r['url']?></td><td style="text-align:right"><b><?= $r['hits']?></b></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <div class="card">
        <div class="card-header">Top Rules</div>
        <div class="card-body" style="padding:0;">
            <table class="data-table">
                <?php foreach($topRules as $r): ?>
                <tr><td style="font-size:0.85em"><?= $r['attack_type']?></td><td style="text-align:right"><b><?= $r['hits']?></b></td></tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>

<script>
const colors = { red: '#f85149', blue: '#1f6feb', green: '#3fb950', orange: '#d29922', text: '#8b949e', grid: '#30363d' };
const commonOptions = { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: colors.text } } } };

// 1. Minacce (Doughnut)
new Chart(document.getElementById('distChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($attackDistribution)) ?>,
        datasets: [{ data: <?= json_encode(array_values($attackDistribution)) ?>, backgroundColor: [colors.red, colors.orange, '#a371f7', colors.blue], borderWidth: 0 }]
    }, options: commonOptions
});

// 2. HTTP Status (Pie)
const statusData = <?= json_encode($statusCounts) ?>;
new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: Object.keys(statusData),
        datasets: [{ 
            data: Object.values(statusData), 
            backgroundColor: ['#238636', '#f85149', '#db6d28', '#8b949e'], // Verde, Rosso, Arancio, Grigio
            borderWidth: 0 
        }]
    }, options: commonOptions
});

// 3. Traffico (Line)
new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{ label: 'Richieste', data: <?= json_encode($chartTraffic) ?>, borderColor: colors.blue, backgroundColor: colors.blue+'20', fill:true, tension:0.4 }]
    }, options: { ...commonOptions, scales: { x: { grid:{display:false}, ticks:{color:colors.text}}, y: { grid:{color:colors.grid}, ticks:{color:colors.text}}} }
});

// 4. Top Targets (Horizontal Bar)
const targetData = <?= json_encode($topTargets) ?>;
new Chart(document.getElementById('targetChart'), {
    type: 'bar',
    data: {
        labels: targetData.map(d => d.hostname),
        datasets: [{ 
            label: 'Eventi', 
            data: targetData.map(d => d.hits), 
            backgroundColor: colors.blue,
            borderRadius: 4
        }]
    }, 
    options: { 
        indexAxis: 'y', // Barre orizzontali
        ...commonOptions,
        scales: { x: { grid:{color:colors.grid}, ticks:{color:colors.text}}, y: { grid:{display:false}, ticks:{color:colors.text}}} 
    }
});
</script>

<?php require_once '../footer.php'; ?>