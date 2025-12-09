<?php
// admin/check_new_orders.php
session_start();
include '../includes/config.php';

// Class sederhana untuk API Notifikasi
class NotificationAPI {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    public function getPendingCount() {
        // Hitung pesanan yang statusnya 'Pending'
        $query = "SELECT COUNT(*) as total FROM orders WHERE status = 'Pending'";
        $result = mysqli_query($this->conn, $query);
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
}

// Eksekusi
$api = new NotificationAPI($conn);
$count = $api->getPendingCount();

// Return dalam format JSON agar bisa dibaca JavaScript
header('Content-Type: application/json');
echo json_encode(['pending_count' => $count]);
?>