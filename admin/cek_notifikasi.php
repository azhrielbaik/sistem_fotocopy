<?php
// cek_notifikasi.php
include '../config.php'; // Sesuaikan path koneksi database kamu

header('Content-Type: application/json');

// Ambil ID pesanan paling besar (terbaru)
$query = mysqli_query($conn, "SELECT MAX(id_pesanan) as last_id FROM pesanan");
$data = mysqli_fetch_assoc($query);

$last_id = $data['last_id'] ? $data['last_id'] : 0;

// Kirim data dalam format JSON
echo json_encode(['last_id' => $last_id]);
?>