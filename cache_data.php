<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if ($data && isset($data['data'])) {
    // Store in session (limited to 1000 records for performance)
    $_SESSION['cached_data'] = array_slice($data['data'], 0, 1000);
    $_SESSION['cache_time'] = time();
    
    // Calculate and cache stats
    $stats = calculateStats($_SESSION['cached_data']);
    $_SESSION['cached_stats'] = $stats;
    
    echo json_encode([
        'success' => true,
        'cached_count' => count($_SESSION['cached_data']),
        'stats' => $stats
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'No data provided']);
}

function calculateStats($data) {
    if (empty($data)) {
        return [
            'total' => 0,
            'avg_twk' => 0,
            'avg_tiu' => 0,
            'avg_tkp' => 0,
            'avg_total' => 0
        ];
    }
    
    $count = count($data);
    $sum_twk = $sum_tiu = $sum_tkp = $sum_total = 0;
    
    foreach ($data as $item) {
        $sum_twk += $item['twk'] ?? 0;
        $sum_tiu += $item['tiu'] ?? 0;
        $sum_tkp += $item['tkp'] ?? 0;
        $sum_total += $item['total'] ?? 0;
    }
    
    return [
        'total' => $count,
        'avg_twk' => round($sum_twk / $count, 1),
        'avg_tiu' => round($sum_tiu / $count, 1),
        'avg_tkp' => round($sum_tkp / $count, 1),
        'avg_total' => round($sum_total / $count, 1)
    ];
}
?>