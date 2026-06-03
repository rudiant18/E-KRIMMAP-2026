<?php
header('Content-Type: application/json');
include 'koneksi.php';

$sql = "SELECT id, kecamatan, desa, titik_koordinat, jenis_kejahatan, tingkat_kerawanan, tanggal_kejadian FROM lokasi_rawan ORDER BY tanggal_kejadian DESC";
$result = $conn->query($sql);
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>