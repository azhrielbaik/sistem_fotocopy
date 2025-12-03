<?php
include '../includes/config.php';

// 1. LOGIKA HAPUS
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM items WHERE id='$id'");
    header("Location: items.php");
}

// 2. LOGIKA SIMPAN (TAMBAH & UPDATE)
if(isset($_POST['save'])) {
    $n = $_POST['name'];
    $c = $_POST['category'];
    $s = $_POST['stock'];
    $p = $_POST['price'];
    $id = $_POST['id'];

    if($id != "") {
        mysqli_query($conn, "UPDATE items SET name='$n', category='$c', stock='$s', price='$p' WHERE id='$id'");
    } else {
        mysqli_query($conn, "INSERT INTO items (name, category, stock, price) VALUES ('$n', '$c', '$s', '$p')");
    }
    header("Location: items.php");
}

// 3. LOGIKA EDIT (Ambil Data)
$data_edit = null;
$show_form = false;
if(isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query_edit = mysqli_query($conn, "SELECT * FROM items WHERE id='$id'");
    $data_edit = mysqli_fetch_assoc($query_edit);
    $show_form = true; // Buka form otomatis saat edit
}

// 4. HITUNG RINGKASAN (Untuk Kartu Atas)
function countCat($conn, $cat) {
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM items WHERE category='$cat'");
    $d = mysqli_fetch_assoc($q);
    return $d['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Data Barang ATK</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); padding: 5px; border-radius: 8px;">
                    <i class="ri-printer-cloud-line" style="font-size: 28px;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 16px;">Admin Panel</h3>
                    <small style="opacity: 0.7; font-size: 11px;">Si-Foprint</small>
                </div>
            </div>

            <ul class="menu">
                <li><a href="manage_orders.php"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan </a></li>
                <li><a href="items.php" class="active"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="charts.php"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="activity_logs.php" class=><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">Admin</h4>
                <small style="opacity: 0.8;">admin@printcopy.com</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="summary-grid">
            <div class="summary-card">
                <div>
                    <h4 style="color: var(--primary); margin:0;">Alat Tulis</h4>
                    <h2 style="margin:5px 0 0;"><?= countCat($conn, 'Alat Tulis') ?> <small style="font-size:12px; color:#666;">Produk</small></h2>
                </div>
                <i class="ri-pencil-ruler-2-line summary-icon"></i>
            </div>
            <div class="summary-card">
                <div>
                    <h4 style="color: var(--primary); margin:0;">Kertas</h4>
                    <h2 style="margin:5px 0 0;"><?= countCat($conn, 'Kertas') ?> <small style="font-size:12px; color:#666;">Produk</small></h2>
                </div>
                <i class="ri-file-paper-2-line summary-icon"></i>
            </div>
            <div class="summary-card">
                <div>
                    <h4 style="color: var(--primary); margin:0;">Buku</h4>
                    <h2 style="margin:5px 0 0;"><?= countCat($conn, 'Buku') ?> <small style="font-size:12px; color:#666;">Produk</small></h2>
                </div>
                <i class="ri-book-mark-line summary-icon"></i>
            </div>
            <div class="summary-card">
                <div>
                    <h4 style="color: var(--primary); margin:0;">Perlengkapan</h4>
                    <h2 style="margin:5px 0 0;"><?= countCat($conn, 'Perlengkapan') ?> <small style="font-size:12px; color:#666;">Produk</small></h2>
                </div>
                <i class="ri-archive-drawer-line summary-icon"></i>
            </div>
        </div>

        <div class="card" style="padding: 25px; min-height: 500px;">
            <div class="page-header-flex">
                <div>
                    <h2 style="color: var(--primary); font-size: 20px; margin-bottom: 5px;">Data Barang ATK</h2>
                    <p style="color: #6B7280; font-size: 13px; margin: 0;">Kelola inventori produk alat tulis kantor</p>
                </div>
                <button onclick="toggleForm()" class="btn-blue" style="background: var(--primary); color: white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:600; display:flex; align-items:center; gap:8px;">
                    <i class="ri-add-line"></i> Tambah Produk
                </button>
            </div>

            <div id="formInput" class="form-container <?= $show_form ? 'show' : '' ?>">
                <h4 style="margin-top:0; margin-bottom:20px;"><?= $data_edit ? 'Edit Produk' : 'Tambah Produk Baru' ?></h4>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $data_edit['id'] ?? '' ?>">
                    
                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                        <div>
                            <label class="form-label">Nama Produk</label>
                            <input type="text" name="name" class="form-control" value="<?= $data_edit['name'] ?? '' ?>" required placeholder="Contoh: Pulpen Standard">
                        </div>
                        <div>
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-control">
                                <?php 
                                $cats = ['Alat Tulis', 'Kertas', 'Buku', 'Perlengkapan'];
                                foreach($cats as $c): 
                                    $sel = ($data_edit['category'] ?? '') == $c ? 'selected' : '';
                                ?>
                                    <option value="<?= $c ?>" <?= $sel ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:15px;">
                        <div>
                            <label class="form-label">Stok</label>
                            <input type="number" name="stock" class="form-control" value="<?= $data_edit['stock'] ?? '' ?>" required placeholder="0">
                        </div>
                        <div>
                            <label class="form-label">Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="<?= $data_edit['price'] ?? '' ?>" required placeholder="0">
                        </div>
                    </div>

                    <div style="margin-top: 20px; text-align: right;">
                        <a href="items.php" style="color: #666; text-decoration: none; margin-right: 15px;">Batal</a>
                        <button name="save" class="btn-blue" style="background: var(--primary); color: white; border:none; padding:10px 25px; border-radius:6px; cursor:pointer;">Simpan</button>
                    </div>
                </form>
            </div>

            <table class="custom-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $q = mysqli_query($conn, "SELECT * FROM items ORDER BY id DESC");
                    while($r = mysqli_fetch_assoc($q)):
                        // Logika Badge Stok
                        $stokClass = 'stok-sedang';
                        $stokLabel = 'Stok Sedang';
                        
                        if($r['stock'] > 100) { $stokClass = 'stok-aman'; $stokLabel = 'Stok Cukup'; }
                        if($r['stock'] < 20)  { $stokClass = 'stok-rendah'; $stokLabel = 'Stok Rendah'; }
                    ?>
                    <tr>
                        <td style="color: #6B7280; font-size:13px;">P00<?= $r['id'] ?></td>
                        <td style="font-weight: 500;"><?= $r['name'] ?></td>
                        <td><span class="badge-cat"><?= $r['category'] ?></span></td>
                        <td><?= $r['stock'] ?> pcs</td>
                        <td style="font-weight:600;"><?= formatRupiah($r['price']) ?></td>
                        <td><span class="badge-stock <?= $stokClass ?>"><?= $stokLabel ?></span></td>
                        <td>
                            <a href="items.php?edit=<?= $r['id'] ?>" class="action-btn view"><i class="ri-pencil-line"></i></a>
                            <a href="items.php?delete=<?= $r['id'] ?>" class="action-btn delete" onclick="return confirm('Hapus?')"><i class="ri-delete-bin-line"></i></a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>

    <script>
        function toggleForm() {
            const form = document.getElementById('formInput');
            if (form.style.display === 'block') {
                form.style.display = 'none';
            } else {
                form.style.display = 'block';
                form.classList.add('show');
            }
        }
    </script>
</body>
</html>