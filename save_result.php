<?php
header("Content-Type: application/json; charset=UTF-8");

// File output hasil
$filename = "hasil.json";

// Jika GET → tampilkan isi data
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    if (file_exists($filename)) {
        echo file_get_contents($filename);
    } else {
        echo json_encode([]);
    }
    exit;
}

// Hanya izinkan POST untuk menyimpan
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "error" => "Method not allowed"]);
    exit;
}

// Ambil data JSON dari body
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit;
}

// Jika file belum ada → buat baru
if (!file_exists($filename)) {
    file_put_contents($filename, "[]");
}

$existing = json_decode(file_get_contents($filename), true);
if (!is_array($existing)) $existing = [];

// Update jika NIK sama
$found = false;
foreach ($existing as $i => $item) {
    if (isset($item["nik"]) && $item["nik"] == $data["nik"]) {
        $existing[$i] = $data;
        $found = true;
        break;
    }
}

if (!$found) {
    $existing[] = $data;
}

// Simpan file
if (file_put_contents($filename, json_encode($existing, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save file"]);
}
?>
