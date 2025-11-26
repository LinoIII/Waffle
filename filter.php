<?php
// FILE: filter.php (ROOT)
require_once 'config.php';
require_once 'functions.php';

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$pageTitle = 'Filter Editor';
require_once 'header.php';

// Gestione Reset
if (isset($_GET['reset'])) {
    unset($_SESSION['filter']);
    header("Location: filter.php");
    exit;
}

// Gestione Salvataggio Filtro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Salviamo tutto in sessione per renderlo GLOBALE
    $_SESSION['filter'] = $_POST;
    
    // Redirect alla dashboard o alla lista eventi filtrata
    header("Location: dashboard/index.php"); 
    exit;
}

// Recupera valori attuali (o default)
$f = $_SESSION['filter'] ?? [];
?>

<div class="main-container">
    <div class="card full-width">
        <div class="card-header" style="background: linear-gradient(90deg, var(--bg-card) 0%, rgba(31, 111, 235, 0.1) 100%);">
            🛠 FILTER EDITOR (Global Context)
        </div>
        
        <form method="POST" action="filter.php" class="card-body">
            
            <div class="filter-container">
                
                <div class="col-left">
                    <div class="filter-section-title">General Criteria</div>

                    <div class="filter-row">
                        <label class="filter-label">Date From</label>
                        <div class="input-group">
                            <input type="date" name="date_from" class="form-control" value="<?= $f['date_from'] ?? date('Y-m-d') ?>">
                            <input type="time" name="time_from" class="form-control" value="<?= $f['time_from'] ?? '00:00:00' ?>">
                        </div>
                    </div>
                    <div class="filter-row">
                        <label class="filter-label">Date To</label>
                        <div class="input-group">
                            <input type="date" name="date_to" class="form-control" value="<?= $f['date_to'] ?? date('Y-m-d') ?>">
                            <input type="time" name="time_to" class="form-control" value="<?= $f['time_to'] ?? '23:59:59' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Sensor</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_sensor" <?= isset($f['not_sensor'])?'checked':'' ?>> Not</label>
                            <select name="sensor" class="form-control">
                                <option value="">All Sensors</option>
                                <option value="1">Apache-Front-01</option> <option value="2">Nginx-LB</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Client IP</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_ip" <?= isset($f['not_ip'])?'checked':'' ?>> Not</label>
                            <input type="text" name="ip" class="form-control" placeholder="192.168.1.1" value="<?= $f['ip'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Country Code</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_cc" <?= isset($f['not_cc'])?'checked':'' ?>> Not</label>
                            <input type="text" name="country_code" class="form-control" placeholder="IT, US, CN" value="<?= $f['country_code'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Action</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_action" <?= isset($f['not_action'])?'checked':'' ?>> Not</label>
                            <select name="action" class="form-control">
                                <option value="">All Actions</option>
                                <optgroup label="Block Actions">
                                    <option value="403">Access denied with code (403)</option>
                                    <option value="conn_close">Access denied with connection close</option>
                                    <option value="redirect">Access denied with redirection</option>
                                </optgroup>
                                <optgroup label="Allow Actions">
                                    <option value="allow">Access allowed</option>
                                    <option value="pass">Access to phase allowed</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Event Severity</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_severity" <?= isset($f['not_severity'])?'checked':'' ?>> Not</label>
                            <select name="severity" class="form-control">
                                <option value="">All Severities</option>
                                <option value="0">EMERGENCY (0)</option>
                                <option value="1">ALERT (1)</option>
                                <option value="2">CRITICAL (2)</option>
                                <option value="3">ERROR (3)</option>
                                <option value="4">WARNING (4)</option>
                                <option value="5">NOTICE (5)</option>
                                <option value="6">INFO (6)</option>
                                <option value="7">DEBUG (7)</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Engine Mode</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_mode" <?= isset($f['not_mode'])?'checked':'' ?>> Not</label>
                            <select name="engine_mode" class="form-control">
                                <option value="">All</option>
                                <option value="On">ENABLED</option>
                                <option value="DetectionOnly">DETECTION ONLY</option>
                            </select>
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Tag</label>
                        <div class="input-group">
                            <label class="not-switch"><input type="checkbox" name="not_tag" <?= isset($f['not_tag'])?'checked':'' ?>> Not</label>
                            <input type="text" list="tag-options" name="tag" class="form-control" placeholder="es. sqli" value="<?= $f['tag'] ?? '' ?>">
                            <datalist id="tag-options">
                                <option value="application-multi">
                                <option value="attack-sqli">
                                <option value="attack-xss">
                                <option value="attack-rfi">
                                <option value="PCI/6.5.2">
                            </datalist>
                        </div>
                    </div>
                </div>

                <div class="col-right">
                    
                    <div class="filter-section-title">Anomaly Scoring</div>
                    
                    <div class="filter-row">
                        <label class="filter-label">Total Score</label>
                        <div class="input-group">
                            <select name="op_total_score" class="operator-select">
                                <option value="ge">≥</option>
                                <option value="le">≤</option>
                                <option value="eq">=</option>
                            </select>
                            <input type="number" name="total_score" class="form-control" value="<?= $f['total_score'] ?? '' ?>">
                        </div>
                    </div>
                    
                    <div class="filter-row">
                        <label class="filter-label">SQLi Score</label>
                        <div class="input-group">
                            <select name="op_sqli_score" class="operator-select"><option value="ge">≥</option></select>
                            <input type="number" name="sqli_score" class="form-control" value="<?= $f['sqli_score'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">XSS Score</label>
                        <div class="input-group">
                            <select name="op_xss_score" class="operator-select"><option value="ge">≥</option></select>
                            <input type="number" name="xss_score" class="form-control" value="<?= $f['xss_score'] ?? '' ?>">
                        </div>
                    </div>

                    <div style="margin-top: 30px;"></div>

                    <div class="filter-section-title">Rule Timing (milliseconds)</div>

                    <div class="filter-row">
                        <label class="filter-label">Duration</label>
                        <div class="input-group">
                            <select name="op_duration" class="operator-select"><option value="ge">≥</option></select>
                            <input type="number" name="duration" class="form-control" value="<?= $f['duration'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Phase 1 (Request)</label>
                        <div class="input-group">
                            <select name="op_p1" class="operator-select"><option value="ge">≥</option></select>
                            <input type="number" name="phase1" class="form-control" value="<?= $f['phase1'] ?? '' ?>">
                        </div>
                    </div>

                    <div class="filter-row">
                        <label class="filter-label">Phase 2 (Response)</label>
                        <div class="input-group">
                            <select name="op_p2" class="operator-select"><option value="ge">≥</option></select>
                            <input type="number" name="phase2" class="form-control" value="<?= $f['phase2'] ?? '' ?>">
                        </div>
                    </div>

                    <div style="margin-top: 30px;"></div>
                     <div class="filter-row">
                        <label class="filter-label">Unique ID</label>
                        <div class="input-group">
                            <input type="text" name="unique_id" class="form-control" placeholder="Unique Trans. ID" value="<?= $f['unique_id'] ?? '' ?>">
                        </div>
                    </div>

                </div>
            </div>

            <div class="action-bar right">
                <a href="filter.php?reset=1" class="btn btn-ghost">🗑 Reset Filtri</a>
                <button type="submit" formaction="export_csv.php" class="btn btn-secondary">📊 Scarica CSV</button>
                <button type="submit" class="btn btn-primary">✅ Applica Filtro Globale</button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>