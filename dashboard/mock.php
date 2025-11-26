<?php
// File: dashboard/mock_generator.php

function getMockStats() {
    return [
        'total_events' => number_format(rand(15000, 20000)),
        'blocked_ip'   => rand(40, 120),
        'waf_status'   => 'Attivo',
        'throughput'   => rand(200, 500) . ' req/s'
    ];
}

function getMockEvents($limit = 10) {
    $attacks = [
        ['type' => 'SQL Injection', 'risk' => 'CRITICAL', 'color' => 'badge-critical'],
        ['type' => 'XSS Attempt', 'risk' => 'HIGH', 'color' => 'badge-high'],
        ['type' => 'Path Traversal', 'risk' => 'MEDIUM', 'color' => 'badge-medium'],
        ['type' => 'Scanner Bot', 'risk' => 'LOW', 'color' => 'badge-low'],
        ['type' => 'Bad User Agent', 'risk' => 'INFO', 'color' => 'badge-info'],
    ];

    $sensors = ['Sensor-Apache-01', 'Nginx-Proxy-LB', 'App-Server-04'];
    $data = [];

    for ($i = 0; $i < $limit; $i++) {
        $atk = $attacks[array_rand($attacks)];
        $data[] = [
            'id' => rand(100000, 999999),
            'timestamp' => date('Y-m-d H:i:s', strtotime("-$i minutes")),
            'sensor' => $sensors[array_rand($sensors)],
            'source_ip' => long2ip(rand(0, "4294967295")),
            'attack_type' => $atk['type'],
            'risk_class' => $atk['color'],
            'action' => (rand(0, 10) > 2) ? 'BLOCKED' : 'LOGGED' // 80% bloccati
        ];
    }
    return $data;
}
?>