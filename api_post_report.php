<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

include 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: ambil laporan (pending + approved)
if ($method === 'GET') {
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $sql = "SELECT * FROM laporan_publik ORDER BY submitted_at DESC";
    if ($status === 'pending') {
        $sql = "SELECT * FROM laporan_publik WHERE status = 'pending' ORDER BY submitted_at DESC";
    } elseif ($status === 'approved') {
        $sql = "SELECT * FROM laporan_publik WHERE status = 'approved' ORDER BY submitted_at DESC";
    }
    $result = $conn->query($sql);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    echo json_encode($data);
    exit;
}

// POST: simpan laporan baru
if ($method === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        echo json_encode(['status'=>'error','message'=>'Data tidak valid']);
        exit;
    }
    
    // Sanitize
    $id = 'r' . time() . rand(100, 999);
    $kategori = $conn->real_escape_string($data['kategori'] ?? '');
    $kecamatan = $conn->real_escape_string($data['kecamatan'] ?? '');
    $lokasi = $conn->real_escape_string($data['lokasi'] ?? '');
    $tanggal = $conn->real_escape_string($data['tanggal'] ?? date('Y-m-d'));
    $lat = isset($data['lat']) && $data['lat'] ? floatval($data['lat']) : 0;
    $lng = isset($data['lng']) && $data['lng'] ? floatval($data['lng']) : 0;
    $severity = intval($data['severity'] ?? 2);
    $deskripsi = $conn->real_escape_string($data['deskripsi'] ?? '');
    $pelapor = $conn->real_escape_string($data['pelapor'] ?? 'Anonim');
    $telp = $conn->real_escape_string($data['telp'] ?? '');
    $status = 'pending';
    $submitted_at = date('Y-m-d H:i:s');
    
    // Cek apakah koordinat valid dalam area
    $isValidArea = false;
    if ($lat && $lng) {
        if ($kecamatan == 'cepu') {
            $isValidArea = ($lat >= -7.18 && $lat <= -7.08 && $lng >= 111.55 && $lng <= 111.63);
        } elseif ($kecamatan == 'padangan') {
            $isValidArea = ($lat >= -7.14 && $lat <= -7.06 && $lng >= 111.46 && $lng <= 111.54);
        }
    } else {
        $isValidArea = true; // Tanpa koordinat, admin yang menentukan nanti
    }
    
    $sql = "INSERT INTO laporan_publik 
            (id, kategori, kecamatan, lokasi, tanggal, lat, lng, severity, deskripsi, pelapor, telp, status, submitted_at)
            VALUES 
            ('$id', '$kategori', '$kecamatan', '$lokasi', '$tanggal', $lat, $lng, $severity, '$deskripsi', '$pelapor', '$telp', '$status', '$submitted_at')";
    
    if ($conn->query($sql)) {
        echo json_encode(['status'=>'success', 'id'=>$id, 'message'=>'Laporan terkirim, menunggu verifikasi admin']);
    } else {
        echo json_encode(['status'=>'error', 'message'=>$conn->error]);
    }
    exit;
}

// DELETE: hapus laporan (untuk admin)
if ($method === 'DELETE') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    $id = $conn->real_escape_string($data['id'] ?? '');
    
    if ($id) {
        $sql = "DELETE FROM laporan_publik WHERE id = '$id'";
        if ($conn->query($sql)) {
            echo json_encode(['status'=>'success']);
        } else {
            echo json_encode(['status'=>'error', 'message'=>$conn->error]);
        }
    } else {
        echo json_encode(['status'=>'error', 'message'=>'ID tidak ditemukan']);
    }
    exit;
}
?>
