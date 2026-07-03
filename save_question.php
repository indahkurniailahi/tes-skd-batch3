<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    echo json_encode(["success" => false, "error" => "Invalid JSON"]);
    exit;
}

$filename = "soal.json";

// Pastikan file bisa ditulis
if (!file_exists($filename)) {
    file_put_contents($filename, "[]");
}

if (!is_writable($filename)) {
    echo json_encode(["success" => false, "error" => "File tidak dapat ditulis"]);
    exit;
}

if (file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT))) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Gagal menyimpan file"]);
}
?>
