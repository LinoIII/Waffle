<?php
// FILE: header.php (ROOT)
if (session_status() === PHP_SESSION_NONE) session_start();

if (!function_exists('base_url')) {
    function base_url($path = '') {
        $currentDir = dirname($_SERVER['SCRIPT_NAME']);
        if (strpos($currentDir, '/dashboard') !== false || strpos($currentDir, '/controller') !== false) {
            $baseDir = dirname($currentDir);
        } else { $baseDir = $currentDir; }
        $baseDir = rtrim(str_replace('\\', '/', $baseDir), '/');
        return $baseDir . '/' . ltrim($path, '/');
    }
}
$pageTitle = $title ?? 'WAF-FLE Console';
$currentUser = $_SESSION['userName'] ?? $_SESSION['username'] ?? 'Admin';

// Recupera i filtri salvati
$f = $_SESSION['filter'] ?? [];
$hasActiveFilter = !empty($f);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('extra/theme.css') ?>?v=<?= time() ?>">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
</head>
<body>

<header class="app-header">
    <div class="header-inner">
        <a href="<?= base_url('dashboard/index.php') ?>" class="brand">
            <span>🛡</span> WAF-FLE
        </a>

        <nav class="nav">
            <a href="<?= base_url('dashboard/index.php') ?>" class="nav-item">Dashboard</a>
            <a href="<?= base_url('dashboard/events.php') ?>" class="nav-item">Eventi</a>
            <a href="<?= base_url('dashboard/livetail.php') ?>" class="nav-item">Live Log</a>
            <a href="<?= base_url('dashboard/geomap.php') ?>" class="nav-item">Mappa</a>
            
            <button type="button" onclick="toggleFilterModal()" class="nav-item" style="background:transparent; border:1px solid var(--border); cursor:pointer; color:var(--primary);">
                <?= $hasActiveFilter ? '🔵 Filtri Attivi' : '🔍 Filtri' ?>
            </button>
        </nav>

        <div class="header-right">
            <button id="themeToggle" class="btn-theme" title="Cambia Tema"><span>🌗</span></button>
            <span class="user-label">User: <strong><?= htmlspecialchars($currentUser) ?></strong></span>
            <a href="<?= base_url('logout.php') ?>" class="btn-logout">Esci</a>
        </div>
    </div>
</header>

<div id="filterModal" class="modal-overlay">
    <div class="modal-content">
        <form method="POST" action="<?= base_url('dashboard/events.php') ?>">
            <div class="modal-header">
                <div class="modal-title">🛠 Configurazione Filtri Globali</div>
                <button type="button" class="btn-close" onclick="toggleFilterModal()">&times;</button>
            </div>
            
            <div class="filter-grid-modal">
                <div class="col-left">
                    <div class="filter-section-title">General Criteria</div>
                    <div class="filter-row"><label class="filter-label">Date From</label><div class="input-group"><input type="date" name="date_from" class="form-control" value="<?= $f['date_from'] ?? date('Y-m-d') ?>"><input type="time" name="time_from" class="form-control" value="<?= $f['time_from'] ?? '00:00:00' ?>"></div></div>
                    <div class="filter-row"><label class="filter-label">Date To</label><div class="input-group"><input type="date" name="date_to" class="form-control" value="<?= $f['date_to'] ?? date('Y-m-d') ?>"><input type="time" name="time_to" class="form-control" value="<?= $f['time_to'] ?? '23:59:59' ?>"></div></div>
                    <div class="filter-row"><label class="filter-label">Sensor</label><div class="input-group"><label class="not-switch"><input type="checkbox" name="not_sensor"> Not</label><select name="sensor" class="form-control"><option value="">All Sensors</option><option value="1">Apache-Front-01</option></select></div></div>
                    <div class="filter-row"><label class="filter-label">Client IP</label><div class="input-group"><label class="not-switch"><input type="checkbox" name="not_ip"> Not</label><input type="text" name="ip" class="form-control" placeholder="192.168..." value="<?= $f['ip'] ?? '' ?>"></div></div>
                    <div class="filter-row"><label class="filter-label">Action</label><div class="input-group"><label class="not-switch"><input type="checkbox" name="not_action"> Not</label><select name="action" class="form-control"><option value="">All Actions</option><option value="403">Blocked (403)</option><option value="allow">Allowed</option></select></div></div>
                    <div class="filter-row"><label class="filter-label">Severity</label><div class="input-group"><label class="not-switch"><input type="checkbox" name="not_severity"> Not</label><select name="severity" class="form-control"><option value="">All</option><option value="5">5 (Critical)</option><option value="1">1 (Low)</option></select></div></div>
                </div>

                <div class="col-right">
                    <div class="filter-section-title">Anomaly Scoring</div>
                    <div class="filter-row"><label class="filter-label">Total Score</label><div class="input-group"><select name="op_total_score" class="operator-select"><option value="ge">≥</option></select><input type="number" name="total_score" class="form-control" value="<?= $f['total_score'] ?? '' ?>"></div></div>
                    <div class="filter-row"><label class="filter-label">SQLi Score</label><div class="input-group"><select name="op_sqli_score" class="operator-select"><option value="ge">≥</option></select><input type="number" name="sqli_score" class="form-control" value="<?= $f['sqli_score'] ?? '' ?>"></div></div>
                    
                    <div style="margin-top:30px;"></div>
                    <div class="filter-section-title">Rule Timing</div>
                    <div class="filter-row"><label class="filter-label">Unique ID</label><div class="input-group"><input type="text" name="unique_id" class="form-control" value="<?= $f['unique_id'] ?? '' ?>"></div></div>
                </div>
            </div>

            <div class="card-footer" style="display:flex; justify-content:flex-end; gap:15px;">
                <a href="<?= base_url('dashboard/events.php?reset=1') ?>" class="btn btn-ghost">🗑 Reset Filtri</a>
                <button type="submit" class="btn btn-primary">✅ Applica</button>
            </div>
        </form>
    </div>
</div>

<main class="main-container">

<script>
function toggleFilterModal() { document.getElementById('filterModal').classList.toggle('active'); }
const toggleBtn = document.getElementById('themeToggle');
if(toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const html = document.documentElement;
        const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
    });
}
</script>