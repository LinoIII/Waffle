<?php
// FILE: export_csv.php (ROOT)
require_once 'config.php';
require_once 'functions.php';

session_start();
if (!isset($_SESSION['user_id'])) exit;

// Nome file con data e ora
$filename = "waf_export_" . date('Ymd_His') . ".csv";

// Headers per il download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');

// Intestazione CSV
fputcsv($out, ['ID', 'Date/Time', 'Source IP', 'Sensor', 'Hostname', 'URL', 'Attack Type', 'Severity', 'Action']);

try {
    if (isset($dbconn)) {
        // --- COSTRUZIONE QUERY FILTRATA (Uguale a events.php) ---
        $sql = "SELECT id, timestamp, source_ip, sensor, hostname, url, attack_type, severity, action 
                FROM sensor_log 
                WHERE 1=1";
        
        $params = [];
        $f = $_SESSION['filter'] ?? [];

        // 1. Date Range
        if (!empty($f['date_from'])) {
            $sql .= " AND timestamp >= :start";
            $params[':start'] = $f['date_from'] . ' ' . ($f['time_from'] ?? '00:00:00');
        }
        if (!empty($f['date_to'])) {
            $sql .= " AND timestamp <= :end";
            $params[':end'] = $f['date_to'] . ' ' . ($f['time_to'] ?? '23:59:59');
        }

        // 2. Filtri Testuali
        if (!empty($f['ip'])) {
            $op = isset($f['not_ip']) ? "NOT LIKE" : "LIKE";
            $sql .= " AND source_ip $op :ip";
            $params[':ip'] = "%" . $f['ip'] . "%";
        }
        if (!empty($f['sensor'])) {
            $op = isset($f['not_sensor']) ? "!=" : "=";
            $sql .= " AND sensor $op :sens"; // Adatta il nome colonna se necessario
            $params[':sens'] = $f['sensor'];
        }
        if (!empty($f['severity'])) {
            $op = isset($f['not_severity']) ? "!=" : "=";
            $sql .= " AND severity $op :sev";
            $params[':sev'] = $f['severity'];
        }
        // ... Aggiungi qui altri filtri se servono (Action, ecc.) ...

        // Ordine e Limite (Aumentato per export)
        $sql .= " ORDER BY timestamp DESC LIMIT 5000";

        $stmt = $dbconn->prepare($sql);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($out, $row);
        }
    }
} catch (Exception $e) {
    // In caso di errore, scrive una riga di avviso nel CSV
    fputcsv($out, ['ERRORE:', 'Impossibile recuperare i dati dal DB.']);
}

fclose($out);
exit;
?>