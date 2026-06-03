<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

include 'koneksi.php';

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

if (!$data) {
    echo json_encode(['status'=>'error','message'=>'Data tidak valid']);
    exit;
}

// Sanitize
$kecamatan       = $conn->real_escape_string($data['kecamatan']     ?? '');
$desa            = $conn->real_escape_string($data['lokasi']        ?? '');
$titik_koordinat = $conn->real_escape_string($data['koordinat']     ?? '');
$jenis_kejahatan = $conn->real_escape_string($data['kategori']      ?? '');
$tingkat_kerawanan = intval($data['severity'] ?? 1);
$tanggal_kejadian  = $conn->real_escape_string($data['tanggal']     ?? date('Y-m-d'));

$sql = "INSERT INTO lokasi_rawan 
        (kecamatan, desa, titik_koordinat, jenis_kejahatan, tingkat_kerawanan, tanggal_kejadian)
        VALUES 
        ('$kecamatan','$desa','$titik_koordinat','$jenis_kejahatan',$tingkat_kerawanan,'$tanggal_kejadian')";

if ($conn->query($sql)) {
    echo json_encode(['status'=>'success','id'=>$conn->insert_id]);
} else {
    echo json_encode(['status'=>'error','message'=>$conn->error]);
}
?>
