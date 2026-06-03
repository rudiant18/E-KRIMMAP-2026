<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

include 'koneksi.php';

$input = file_get_contents('php://input');
$data = json_decode($input, true);
$id = $conn->real_escape_string($data['id'] ?? '');

if (!$id) {
    echo json_encode(['status'=>'error', 'message'=>'ID tidak valid']);
    exit;
}

// Ambil data laporan
$sql = "SELECT * FROM laporan_publik WHERE id = '$id'";
$result = $conn->query($sql);
$report = $result->fetch_assoc();

if (!$report) {
    echo json_encode(['status'=>'error', 'message'=>'Laporan tidak ditemukan']);
    exit;
}

// Update status laporan
$sql = "UPDATE laporan_publik SET status = 'approved' WHERE id = '$id'";
if ($conn->query($sql)) {
    // Masukkan ke tabel lokasi_rawan
    $kecamatan = $conn->real_escape_string($report['kecamatan']);
    $desa = $conn->real_escape_string($report['lokasi']);
    $titik_koordinat = ($report['lat'] && $report['lng']) ? $report['lat'] . ',' . $report['lng'] : '';
    $jenis_kejahatan = $conn->real_escape_string($report['kategori']);
    $tingkat_kerawanan = intval($report['severity']);
    $tanggal_kejadian = $conn->real_escape_string($report['tanggal']);
    $judul = $conn->real_escape_string(ucfirst($report['kategori']) . ' di ' . $report['lokasi']);
    
    $sql2 = "INSERT INTO lokasi_rawan 
             (kecamatan, desa, titik_koordinat, jenis_kejahatan, tingkat_kerawanan, tanggal_kejadian, judul, deskripsi)
             VALUES 
             ('$kecamatan', '$desa', '$titik_koordinat', '$jenis_kejahatan', $tingkat_kerawanan, '$tanggal_kejadian', '$judul', '{$conn->real_escape_string($report['deskripsi'])}')";
    
    if ($conn->query($sql2)) {
        echo json_encode(['status'=>'success', 'message'=>'Laporan disetujui dan ditambahkan ke peta']);
    } else {
        echo json_encode(['status'=>'error', 'message'=>'Gagal menambah ke peta: ' . $conn->error]);
    }
} else {
    echo json_encode(['status'=>'error', 'message'=>$conn->error]);
}
?>
