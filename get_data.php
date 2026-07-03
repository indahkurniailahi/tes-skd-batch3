<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Cache-Control: max-age=300"); // Cache 5 minutes

$filename = 'hasil.json';

// Check if file exists
if (!file_exists($filename)) {
    echo json_encode(['results' => [], 'timestamp' => time(), 'count' => 0]);
    exit();
}

// Get file modification time
$filemtime = filemtime($filename);

// Check cache headers
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $if_modified_since = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($if_modified_since >= $filemtime) {
        header('HTTP/1.1 304 Not Modified');
        exit();
    }
}

// Set cache headers
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $filemtime) . ' GMT');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 300) . ' GMT');

// Read and output file
$data = file_get_contents($filename);
$results = json_decode($data, true);

// Optimize: Limit fields if needed
if (isset($_GET['minimal']) && $_GET['minimal'] == '1') {
    $minimalResults = array_map(function($item) {
        return [
            'nama' => $item['nama'] ?? '',
            'nik' => $item['nik'] ?? '',
            'formasi1' => $item['formasi1'] ?? '',
            'formasi2' => $item['formasi2'] ?? '',
            'twk' => $item['twk'] ?? 0,
            'tiu' => $item['tiu'] ?? 0,
            'tkp' => $item['tkp'] ?? 0,
            'total' => $item['total'] ?? 0,
            'percentage' => $item['percentage'] ?? 0,
            'submitted_at' => $item['submitted_at'] ?? ''
        ];
    }, $results);
    
    echo json_encode([
        'results' => $minimalResults,
        'timestamp' => time(),
        'count' => count($results),
        'cached' => true
    ]);
} else {
    echo json_encode([
        'results' => $results,
        'timestamp' => time(),
        'count' => count($results)
    ]);
}
?>