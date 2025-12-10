<?php
// admin/get_users_table.php
session_start();
include '../includes/config.php';

// Logic Pagination (Sama seperti manage_users.php)
$limit = 5;
$halaman = isset($_GET['halaman']) ? (int)$_GET['halaman'] : 1;
$start = ($halaman > 1) ? ($halaman * $limit) - $limit : 0;
$nomor = $start + 1;

// Query Data
$query = "SELECT * FROM users 
          ORDER BY created_at DESC 
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Helper function untuk status (sama dengan di class)
function getUserStatus($conn, $userId) {
    $q = mysqli_query($conn, "SELECT action, created_at FROM activity_logs WHERE user_id='$userId' ORDER BY id DESC LIMIT 1");
    if(mysqli_num_rows($q) > 0) {
        $row = mysqli_fetch_assoc($q);
        if($row['action'] == 'Login') {
            return ['dot' => 'online', 'text' => 'Online', 'last_seen' => $row['created_at']];
        }
    }
    return ['dot' => 'offline', 'text' => 'Offline', 'last_seen' => null];
}

if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)):
        $status = getUserStatus($conn, $row['id']);
        $joinDate = isset($row['created_at']) ? date('d M Y, H:i', strtotime($row['created_at'])) : '-';
?>
    <tr>
        <td><?= $nomor++ ?></td>
        <td>
            <div style="display: flex; align-items: center;">
                <div style="width:35px; height:35px; background:#e0e7ff; color:#3730a3; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:bold; margin-right:10px;">
                    <?= strtoupper(substr($row['username'], 0, 1)) ?>
                </div>
                <div>
                    <strong><?= $row['username'] ?></strong>
                    <br>
                    <small style="color:#888;">ID: <?= $row['id'] ?> • <span style="text-transform:capitalize;"><?= $row['role'] ?></span></small>
                </div>
            </div>
        </td>
        <td>
            <div style="display: flex; align-items: center;">
                <span class="status-dot <?= $status['dot'] ?>"></span>
                <span style="font-size:13px; font-weight:500; color:#555;"><?= $status['text'] ?></span>
            </div>
            <?php if($status['last_seen']): ?>
                <small style="color:#999; font-size:10px; margin-left:18px;">Last active: <?= date('H:i', strtotime($status['last_seen'])) ?></small>
            <?php endif; ?>
        </td>
        <td style="color: #6B7280; font-size: 13px;">
            <i class="ri-calendar-line" style="vertical-align:middle; margin-right:5px;"></i>
            <?= $joinDate ?>
        </td>
        <td>
            <?php if($row['id'] != $_SESSION['user_id']): ?>
                <a href="manage_users.php?delete=<?= $row['id'] ?>" class="action-btn delete" onclick="return confirm('Yakin ingin menghapus user ini?')" title="Hapus User">
                    <i class="ri-delete-bin-line"></i>
                </a>
            <?php else: ?>
                <span style="font-size:11px; color:#aaa; font-style:italic;">(Akun Anda)</span>
            <?php endif; ?>
        </td>
    </tr>
<?php 
    endwhile;
} else {
    echo '<tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">Belum ada user.</td></tr>';
}
?>