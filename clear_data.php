<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Files to clear
$files = ['hasil.json'];
$results = [];

foreach ($files as $file) {
    if (file_exists($file)) {
        // Create backup before clearing
        $backupDir = 'backup';
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }
        $backupFile = $backupDir . '/clear_backup_' . date('Y-m-d_H-i-s') . '.json';
        copy($file, $backupFile);
        
        // Clear the file
        if (file_put_contents($file, json_encode([], JSON_PRETTY_PRINT))) {
            $results[$file] = [
                'status' => 'cleared',
                'backup' => $backupFile
            ];
        } else {
            $results[$file] = [
                'status' => 'failed',
                'error' => 'Could not write to file'
            ];
        }
    } else {
        // Create empty file if doesn't exist
        file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
        $results[$file] = ['status' => 'created'];
    }
}

// Check if all operations were successful
$allSuccess = true;
foreach ($results as $result) {
    if (isset($result['status']) && $result['status'] === 'failed') {
        $allSuccess = false;
        break;
    }
}

if ($allSuccess) {
    echo json_encode([
        'success' => true, 
        'message' => 'Semua data berhasil dihapus',
        'details' => $results,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'error' => 'Beberapa file gagal dihapus',
        'details' => $results
    ]);
}
?>