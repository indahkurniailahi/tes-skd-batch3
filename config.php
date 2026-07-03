<?php
// Enable CORS untuk akses dari device lain
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Jika menggunakan MySQL, sesuaikan konfigurasi ini
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "ujian_karyawan";

// Fungsi koneksi database (jika pakai MySQL)
function connectDB() {
    global $db_host, $db_user, $db_pass, $db_name;
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if ($conn->connect_error) {
        die(json_encode(["error" => "Koneksi database gagal"]));
    }
    return $conn;
}

// Simpan ke file JSON (lebih sederhana)
function saveToFile($filename, $data) {
    $existing = [];
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        if (!empty($content)) {
            $existing = json_decode($content, true);
        }
    }
    
    // Jika data sudah ada (berdasarkan NIK), update
    if (isset($data['nik'])) {
        $found = false;
        foreach ($existing as $key => $item) {
            if (isset($item['nik']) && $item['nik'] == $data['nik']) {
                $existing[$key] = $data;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $existing[] = $data;
        }
    } else {
        $existing[] = $data;
    }
    
    file_put_contents($filename, json_encode($existing, JSON_PRETTY_PRINT));
    return true;
}

// Ambil data dari file
function getFromFile($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    $content = file_get_contents($filename);
    return json_decode($content, true) ?: [];
}
?>