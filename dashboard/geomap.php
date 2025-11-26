<?php
// FILE: dashboard/geomap.php
require_once '../config.php';
require_once '../functions.php';

session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../index.php"); exit; }

$pageTitle = 'Mappa Minacce';
require_once '../header.php';

// --- LOGICA DATI ---
$mapPoints = [];
$filterStatus = "Tutti i dati (Nessun filtro)";

try {
    if (isset($dbconn)) {
        // Query di base
        $sql = "SELECT source_ip, lat, lon, MAX(severity) as max_sev, COUNT(*) as hits 
                FROM sensor_log 
                WHERE lat IS NOT NULL AND lon IS NOT NULL";
        
        $params = [];
        $f = $_SESSION['filter'] ?? [];

        // Applichiamo i filtri globali
        if (!empty($f['date_from'])) {
            $sql .= " AND timestamp >= :start";
            $params[':start'] = $f['date_from'] . ' ' . ($f['time_from'] ?? '00:00:00');
            $filterStatus = "Filtro Attivo: Date Range";
        }
        if (!empty($f['date_to'])) {
            $sql .= " AND timestamp <= :end";
            $params[':end'] = $f['date_to'] . ' ' . ($f['time_to'] ?? '23:59:59');
        }
        if (!empty($f['ip'])) {
            $sql .= " AND source_ip LIKE :ip";
            $params[':ip'] = "%" . $f['ip'] . "%";
            $filterStatus = "Filtro Attivo: IP " . htmlspecialchars($f['ip']);
        }
        if (!empty($f['severity'])) {
            $sql .= " AND severity = :sev";
            $params[':sev'] = $f['severity'];
            $filterStatus = "Filtro Attivo: Severity " . htmlspecialchars($f['severity']);
        }

        $sql .= " GROUP BY source_ip, lat, lon LIMIT 1000";

        $stmt = $dbconn->prepare($sql);
        $stmt->execute($params);
        $mapPoints = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Exception $e) { /* Silent fail */ }

// --- FALLBACK MOCK (Solo se non ci sono risultati e nessun filtro attivo) ---
// Questo serve per farti vedere la mappa colorata correttamente se il DB è vuoto
if (empty($mapPoints) && empty($_SESSION['filter'])) {
    $mapPoints = [
        // Esempio ROSSO (Alto - Severity 5)
        ['lat'=>41.90, 'lon'=>12.49, 'source_ip'=>'192.168.1.5', 'max_sev'=>5, 'hits'=>10],
        // Esempio VERDE (Basso - Severity 1)
        ['lat'=>40.71, 'lon'=>-74.00, 'source_ip'=>'10.0.0.1', 'max_sev'=>1, 'hits'=>50],
        // Esempio GIALLO (Medio - Severity 3)
        ['lat'=>51.50, 'lon'=>-0.12, 'source_ip'=>'80.80.80.80', 'max_sev'=>3, 'hits'=>5],
    ];
    $filterStatus = "Modalità Demo (Dati Simulati)";
}
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
    .leaflet-container { background: #161b22 !important; }
    .map-legend {
        padding: 10px; background: var(--bg-card); border-bottom: 1px solid var(--border);
        font-size: 0.8rem; display: flex; gap: 15px; align-items: center; color: var(--text-muted);
    }
    .dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
</style>

<div class="main-container">
    <div class="card full-width">
        <div class="card-header">Geolocalizzazione Attacchi</div>
        
        <div class="map-legend">
            <span>Status: <strong><?= $filterStatus ?></strong></span>
            <span style="flex:1"></span>
            <span><span class="dot" style="background:#3fb950; box-shadow:0 0 5px #3fb950;"></span>Severity 1-2 (Basso)</span>
            <span><span class="dot" style="background:#d29922; box-shadow:0 0 5px #d29922;"></span>Severity 3 (Medio)</span>
            <span><span class="dot" style="background:#f85149; box-shadow:0 0 5px #f85149;"></span>Severity 4-5+ (Alto)</span>
        </div>

        <div class="card-body" style="padding:0;">
            <div id="threat-map" style="height: 600px; width: 100%;"></div>
        </div>
    </div>
</div>

<script>
    const points = <?= json_encode($mapPoints) ?>;
    
    // Inizializza Mappa
    var map = L.map('threat-map', {
        center: [20, 0], 
        zoom: 2,
        minZoom: 2,
        maxBounds: [[-90, -180], [90, 180]],
        maxBoundsViscosity: 1.0
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap',
        subdomains: 'abcd',
        maxZoom: 19,
        noWrap: true,
        bounds: [[-90, -180], [90, 180]]
    }).addTo(map);

    // --- FUNZIONE COLORI CORRETTA ---
    function getMarkerColor(severity) {
        let s = parseInt(severity);
        
        // 1. Se la Severity è ALTA (4 o 5 o più) -> ROSSO
        if (s >= 4) return '#f85149'; 
        
        // 2. Se la Severity è MEDIA (3) -> ARANCIO
        if (s === 3) return '#d29922';
        
        // 3. Altrimenti (1 o 2) -> VERDE
        return '#3fb950';
    }

    // Disegna i punti
    points.forEach(pt => {
        let color = getMarkerColor(pt.max_sev);
        
        let iconHtml = `<div style='
            background-color: ${color};
            width: 12px; height: 12px;
            border-radius: 50%;
            box-shadow: 0 0 8px ${color};
            border: 1px solid #fff;
        '></div>`;

        let customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: iconHtml,
            iconSize: [12, 12],
            iconAnchor: [6, 6]
        });

        L.marker([pt.lat, pt.lon], {icon: customIcon})
         .addTo(map)
         .bindPopup(`
            <div style="color:#333; font-family:sans-serif;">
                <b>IP:</b> ${pt.source_ip}<br>
                <b>Eventi:</b> ${pt.hits}<br>
                <b>Severity Max:</b> ${pt.max_sev}
            </div>
         `);
    });
</script>

<?php require_once '../footer.php'; ?>