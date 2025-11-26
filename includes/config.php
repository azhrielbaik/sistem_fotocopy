<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_tikafotocopy";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Fungsi Format Rupiah Global
function formatRupiah($angka){
    return "Rp " . number_format($angka,0,',','.');
}
?>