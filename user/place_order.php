<?php
session_start();
include '../includes/config.php';

class OrderHandler {
    private $conn;
    private $userId;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->checkLogin();
        $this->userId = $_SESSION['user_id'];
    }

    // 1. CEK LOGIN
    private function checkLogin() {
        if(!isset($_SESSION['user_id'])){
            header("Location: ../login.php");
            exit();
        }
    }

    // Router untuk menangani form submission
    public function handleRequests() {
        if(isset($_POST['submit_print'])) {
            $this->processPrintOrder();
        }
        if(isset($_POST['submit_atk'])) {
            $this->processAtkOrder();
        }
    }

    // --- LOGIC 1: SIMPAN PESANAN PRINT ---
    private function processPrintOrder() {
        $order_id = "ORD-" . rand(1000,9999);
        $jenis = $_POST['print_type'];
        $kertas = $_POST['paper_size'];
        $jilid = $_POST['binding'];
        $qty = $_POST['qty'];
        $catatan = $_POST['notes'];
        $payment_method = $_POST['payment_method'];

        // Logic Upload File
        $file_name = null;
        if(isset($_FILES['print_file']) && $_FILES['print_file']['error'] == 0){
            $target_dir = "../uploads/"; 
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            
            $file_ext = pathinfo($_FILES['print_file']['name'], PATHINFO_EXTENSION);
            $file_name = "DOC_" . time() . "_" . $this->userId . "." . $file_ext;
            $target_file = $target_dir . $file_name;
            
            move_uploaded_file($_FILES['print_file']['tmp_name'], $target_file);
        }

        // Perhitungan Harga
        $harga_lembar = ($jenis == 'Hitam Putih') ? 500 : 2000;
        $harga_jilid = ($jilid == 'Spiral') ? 10000 : (($jilid == 'Hard Cover') ? 20000 : 0);
        $total = ($harga_lembar * $qty) + $harga_jilid;
        
        $detail = "Print: $jenis ($kertas), Jilid: $jilid. Note: $catatan";
        
        // Simpan ke Database
        $query = "INSERT INTO orders (id, user_id, type, items, total_price, payment_method, file_name, status, created_at) 
                  VALUES ('$order_id', '$this->userId', 'Print', '$detail', '$total', '$payment_method', '$file_name', 'Pending', NOW())";
        
        if(mysqli_query($this->conn, $query)){
            echo "<script>alert('Pesanan Print Berhasil!'); window.location='view_orders.php';</script>";
        } else {
            echo "<script>alert('Gagal: ".mysqli_error($this->conn)."');</script>";
        }
    }

    // --- LOGIC 2: SIMPAN PESANAN ATK ---
    private function processAtkOrder() {
        $order_id = "ORD-" . rand(1000,9999);
        $items_json = $_POST['cart_data']; 
        $total = $_POST['cart_total'];
        $payment_method = $_POST['payment_method_atk']; 
        
        $cart_array = json_decode($items_json, true);
        $detail_str = "";
        
        if($cart_array && count($cart_array) > 0) {
            foreach($cart_array as $item) {
                $detail_str .= $item['name'] . " (" . $item['qty'] . "x), ";
                $id_barang = $item['id'];
                $qty_beli = $item['qty'];
                // Update Stok
                mysqli_query($this->conn, "UPDATE items SET stock = stock - $qty_beli WHERE id='$id_barang'");
            }
            $detail_str = rtrim($detail_str, ", ");

            if(!empty($detail_str)) {
                $query = "INSERT INTO orders (id, user_id, type, items, total_price, payment_method, status, created_at) 
                          VALUES ('$order_id', '$this->userId', 'ATK', '$detail_str', '$total', '$payment_method', 'Pending', NOW())";
                
                mysqli_query($this->conn, $query);
                echo "<script>alert('Pesanan ATK Berhasil!'); window.location='view_orders.php';</script>";
            }
        } else {
            echo "<script>alert('Keranjang kosong!');</script>";
        }
    }

    // --- LOGIC 3: AMBIL DATA PRODUK UNTUK TAMPILAN ---
    public function getAtkProducts() {
        return mysqli_query($this->conn, "SELECT * FROM items ORDER BY id DESC");
    }
}

// --- EKSEKUSI PROGRAM ---
$orderPage = new OrderHandler($conn);
$orderPage->handleRequests();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Buat Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Styling Tab & Layout */
        .tab-container { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab-btn { padding: 10px 20px; border: none; background: #e5e7eb; border-radius: 8px; cursor: pointer; font-weight: 600; color: #4b5563; transition: 0.3s; }
        .tab-btn.active { background: #2563EB; color: white; }
        .tab-content { display: none; animation: fadeIn 0.3s; }
        .tab-content.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Styling Upload File */
        .file-upload-box { background: #f8fafc; border: 2px dashed #cbd5e1; padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 20px; transition: 0.3s; }
        .file-upload-box:hover { border-color: #2563EB; background: #eff6ff; }
        
        /* Styling Pilihan Pembayaran */
        .payment-options { display: flex; gap: 15px; margin-top: 10px; }
        .payment-card { flex: 1; cursor: pointer; }
        .payment-card input { display: none; }
        .p-card-content { border: 2px solid #E5E7EB; padding: 15px; border-radius: 10px; text-align: center; transition: 0.3s; background: #fff; color: #475569; }
        .p-card-content i { font-size: 24px; margin-bottom: 5px; display: block; }
        .payment-card input:checked + .p-card-content { border-color: #2563EB; background-color: #EFF6FF; color: #2563EB; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15); }
        .icon-cash { color: #10b981; }
        .icon-qris { color: #3b82f6; }

        /* Opsi Kecil untuk Sidebar ATK */
        .small-options { gap: 8px; }
        .p-small { padding: 8px; font-size: 13px; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .p-small i { font-size: 14px; margin: 0; display: inline; }

        /* Styling Umum Card & Grid */
        .card-clean { margin-top: 20px; border: none; padding: 25px; background: white; border-radius: 15px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .selection-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .selection-grid.col-3 { grid-template-columns: 1fr 1fr 1fr; }
        .selection-box { border: 2px solid #E5E7EB; padding: 15px; border-radius: 10px; cursor: pointer; text-align: center; background: #fff; }
        .selection-box.selected { border-color: #2563EB; background-color: #EFF6FF; color: #2563EB; }
        .selection-box input { display: none; }
        .bg-gray { background-color: #F9FAFB; border: 1px solid #E5E7EB; }
        .estimate-box { background: #EFF6FF; padding: 15px; border-radius: 10px; margin: 20px 0; display: flex; justify-content: space-between; align-items: center; border: 1px solid #DBEAFE; color: #1E40AF; }

        /* ATK & Cart Layout */
        .atk-layout { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; }
        .product-card { background: white; padding: 15px; border-radius: 10px; text-align: center; border: 1px solid #eee; transition: 0.3s; position: relative; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
        .prod-img { width: 100%; height: 100px; object-fit: contain; margin-bottom: 10px; }
        .btn-add { margin-top: 10px; padding: 6px 12px; border: none; background: #2563EB; color: white; border-radius: 6px; cursor: pointer; width: 100%; }
        .btn-disabled { background-color: #e2e8f0 !important; color: #94a3b8 !important; cursor: not-allowed; }
        .badge { font-size: 10px; padding: 2px 8px; border-radius: 4px; display: inline-block; margin-bottom: 5px; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f3f4f6; color: #374151; }

        /* Sidebar Keranjang */
        .cart-sidebar { background: white; padding: 20px; border-radius: 12px; height: fit-content; border: 1px solid #eee; position: sticky; top: 20px; }
        .btn-full { width: 100%; padding: 12px; background: #2563EB; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        .cart-item { margin-bottom: 10px; border-bottom: 1px dashed #eee; padding-bottom: 8px; }
        .qty-control button { border: 1px solid #ddd; background: #fff; width: 24px; height: 24px; cursor: pointer; border-radius: 4px; }

        /* Modal QRIS */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px); }
        .modal-content { background: white; padding: 30px; border-radius: 20px; width: 90%; max-width: 400px; text-align: center; animation: popUp 0.3s ease-out; }
        @keyframes popUp { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }
        .qris-img { width: 180px; height: 180px; margin: 15px 0; border: 1px solid #eee; border-radius: 8px; }
        .btn-success { background: #10b981; color: white; width: 100%; padding: 12px; border:none; border-radius: 10px; font-weight: 600; cursor: pointer; }
        .btn-link { background: none; border: none; color: #64748b; margin-top: 15px; cursor: pointer; text-decoration: underline; font-size: 13px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px;">Dashboard</h3>
                    <small style="opacity: 0.8; font-size: 12px;">Si-Foprint</small>
                </div>
            </div>
            <ul class="menu">
                <li><a href="dashboard.php"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php" class="active"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
                <li><a href="profile.php"><i class="ri-user-settings-line"></i> Profil Saya</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;"><?= $_SESSION['username'] ?></h4>
                <small style="opacity: 0.8;">User Account</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="header" style="justify-content: center; position: relative; margin-bottom:20px; display:flex;">
            <a href="dashboard.php" style="position: absolute; left: 0; color: #333; text-decoration: none;">
                <i class="ri-close-line" style="font-size: 24px;"></i>
            </a>
            <h4 style="color: var(--primary);">Buat Pesanan</h4>
        </div>

        <div class="tab-container">
            <button class="tab-btn active" onclick="switchTab('print')"><i class="ri-printer-line"></i> Print & Fotocopy</button>
            <button class="tab-btn" onclick="switchTab('atk')"><i class="ri-box-3-line"></i> Produk ATK</button>
        </div>

        <div id="tab-print" class="tab-content active">
            <div class="card-clean">
                <form method="POST" id="formPrint" enctype="multipart/form-data">
                    
                    <div class="file-upload-box">
                        <label style="display:block; margin-bottom:10px; font-weight:600;"><i class="ri-file-upload-line"></i> Upload Dokumen</label>
                        <input type="file" name="print_file" class="form-control" required style="width:90%; padding:10px;">
                        <div style="margin-top:5px;"><small style="color:#64748b;">(PDF/Word/Gambar - Maks 5MB)</small></div>
                    </div>

                    <label class="form-label">Jenis Print</label>
                    <div class="selection-grid">
                        <label class="selection-box selected" onclick="selectBox(this)">
                            <input type="radio" name="print_type" value="Hitam Putih" checked onchange="calcTotal()">
                            <strong>Hitam Putih</strong><br><small>Rp 500/lbr</small>
                        </label>
                        <label class="selection-box" onclick="selectBox(this)">
                            <input type="radio" name="print_type" value="Berwarna" onchange="calcTotal()">
                            <strong>Berwarna</strong><br><small>Rp 2.000/lbr</small>
                        </label>
                    </div>

                    <label class="form-label">Ukuran Kertas</label>
                    <div class="selection-grid">
                        <label class="selection-box selected" onclick="selectBox(this)">
                            <input type="radio" name="paper_size" value="A4" checked onchange="calcTotal()"> <strong>A4</strong>
                        </label>
                        <label class="selection-box" onclick="selectBox(this)">
                            <input type="radio" name="paper_size" value="F4" onchange="calcTotal()"> <strong>F4</strong>
                        </label>
                    </div>

                    <label class="form-label">Jumlah Lembar</label>
                    <input type="number" id="qty" name="qty" class="form-control bg-gray" value="1" min="1" oninput="calcTotal()" style="width:100%; padding:10px; border-radius:8px; margin-bottom:15px;">

                    <label class="form-label">Jilid (Opsional)</label>
                    <div class="selection-grid col-3">
                        <label class="selection-box selected" onclick="selectBox(this)">
                            <input type="radio" name="binding" value="Tidak Jilid" checked onchange="calcTotal()"> <strong>Tidak</strong>
                        </label>
                        <label class="selection-box" onclick="selectBox(this)">
                            <input type="radio" name="binding" value="Spiral" onchange="calcTotal()"> <strong>Spiral</strong><br><small>+10k</small>
                        </label>
                        <label class="selection-box" onclick="selectBox(this)">
                            <input type="radio" name="binding" value="Hard Cover" onchange="calcTotal()"> <strong>Hard</strong><br><small>+20k</small>
                        </label>
                    </div>

                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control bg-gray" rows="3" placeholder="Contoh: Print bolak-balik ya mas..." style="width:100%; padding:10px; border-radius:8px;"></textarea>

                    <label class="form-label" style="display:block; margin-top:20px; font-weight:600;">Metode Pembayaran</label>
                    <div class="payment-options">
                        <label class="payment-card">
                            <input type="radio" name="payment_method" value="Cash" checked>
                            <div class="p-card-content"><i class="fas fa-money-bill-wave icon-cash"></i> <div>Tunai</div></div>
                        </label>
                        <label class="payment-card">
                            <input type="radio" name="payment_method" value="QRIS">
                            <div class="p-card-content"><i class="fas fa-qrcode icon-qris"></i> <div>QRIS</div></div>
                        </label>
                    </div>

                    <div class="estimate-box">
                        <div><strong>Total Estimasi:</strong><br><span id="detail-text">Hitam Putih • 1 lembar</span></div>
                        <div id="total-price" style="font-size:18px; font-weight:bold;">Rp 500</div>
                        <input type="hidden" id="raw_total_price" value="500">
                    </div>

                    <button type="button" onclick="checkPayment('formPrint', 'raw_total_price', 'btnSubmitPrint')" class="btn btn-full">Buat Pesanan Print</button>
                    <button type="submit" name="submit_print" id="btnSubmitPrint" style="display:none;"></button>
                </form>
            </div>
        </div>

        <div id="tab-atk" class="tab-content">
            <div class="atk-layout">
                <div class="product-grid">
                    <?php
                    // MENGAMBIL DATA PRODUK VIA METHOD CLASS
                    $products = $orderPage->getAtkProducts();
                    
                    while($p = mysqli_fetch_assoc($products)):
                        $stok = $p['stock'];
                        $isHabis = ($stok <= 0);
                        $btnDisabled = $isHabis ? 'disabled' : '';
                        $btnText = $isHabis ? 'Habis' : '<i class="ri-add-line"></i> Tambah';
                        $badgeClass = $isHabis ? 'badge-danger' : 'badge-gray';
                    ?>
                    <div class="product-card">
                        <?php if(!empty($p['image'])): ?>
                            <img src="../assets/images/<?= $p['image'] ?>" class="prod-img">
                        <?php else: ?>
                            <i class="ri-box-3-fill product-icon"></i>
                        <?php endif; ?>
                        <h4 style="margin:0; font-size:14px;"><?= $p['name'] ?></h4>
                        <span class="badge <?= $badgeClass ?>">Stok: <?= $stok ?></span>
                        <div class="price-tag" style="font-weight:bold; color:#2563EB;">Rp <?= number_format($p['price'],0,',','.') ?></div>
                        <button class="btn-add <?= $isHabis ? 'btn-disabled' : '' ?>" 
                            onclick="addToCart(<?= $p['id'] ?>, '<?= $p['name'] ?>', <?= $p['price'] ?>, <?= $stok ?>)" <?= $btnDisabled ?>>
                            <?= $btnText ?>
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="cart-sidebar">
                    <h3 style="display:flex; align-items:center; gap:10px; font-size: 16px; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                        <i class="ri-shopping-cart-line"></i> Keranjang
                    </h3>
                    <div id="cart-items-container"><p style="color:#999; font-size:13px; text-align:center;">Keranjang kosong.</p></div>
                    
                    <div style="border-top:1px solid #eee; padding-top:15px; margin-top:15px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold; margin-bottom:15px;">
                            <span>Total:</span> <span id="cart-total-display">Rp 0</span>
                        </div>
                        
                        <form method="POST" id="formATK">
                            <input type="hidden" name="cart_data" id="input_cart_data">
                            <input type="hidden" name="cart_total" id="input_cart_total">
                            <input type="hidden" id="raw_atk_total" value="0">
                            
                            <div style="margin-bottom: 15px;">
                                <label style="font-size: 13px; font-weight: 600;">Metode Pembayaran:</label>
                                <div class="payment-options small-options">
                                    <label class="payment-card">
                                        <input type="radio" name="payment_method_atk" value="Cash" checked>
                                        <div class="p-card-content p-small"><i class="fas fa-money-bill-wave"></i> Tunai</div>
                                    </label>
                                    <label class="payment-card">
                                        <input type="radio" name="payment_method_atk" value="QRIS">
                                        <div class="p-card-content p-small"><i class="fas fa-qrcode"></i> QRIS</div>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="button" onclick="checkPayment('formATK', 'raw_atk_total', 'btnSubmitATK')" class="btn btn-full">Checkout</button>
                            <button type="submit" name="submit_atk" id="btnSubmitATK" style="display:none;"></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="qrisModal" class="modal-overlay">
        <div class="modal-content">
            <h3>Scan QRIS</h3>
            <p>Silakan scan untuk menyelesaikan pembayaran.</p>
            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/QR_code_for_mobile_English_Wikipedia.svg" alt="QRIS" class="qris-img">
            <div style="background:#f8fafc; padding:12px; border-radius:8px; margin-bottom:20px;">
                <p style="margin:0; font-size:14px;">Total Tagihan:</p>
                <h2 id="qrisTotalDisplay" style="margin:0; color:#2563EB;">Rp 0</h2>
            </div>
            <button type="button" onclick="confirmQris()" class="btn-success">Sudah Bayar & Proses</button>
            <button type="button" onclick="closeQris()" class="btn-link">Batal / Ganti Metode</button>
        </div>
    </div>

    <script>
        // --- 1. TAB SWITCHING ---
        function switchTab(tabName) {
            document.getElementById('tab-print').classList.remove('active');
            document.getElementById('tab-atk').classList.remove('active');
            let buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));
            document.getElementById('tab-' + tabName).classList.add('active');
            // Menandai tombol yang diklik sebagai active
            const activeBtn = Array.from(buttons).find(btn => btn.getAttribute('onclick').includes(tabName));
            if(activeBtn) activeBtn.classList.add('active');
        }

        // --- 2. PRINT CALCULATOR LOGIC ---
        function selectBox(el) {
            let siblings = el.parentElement.children;
            for(let sib of siblings) sib.classList.remove('selected');
            el.classList.add('selected');
            el.querySelector('input').checked = true;
            calcTotal();
        }

        function calcTotal() {
            if(!document.getElementById('qty')) return; 
            let qty = document.getElementById('qty').value || 0;
            let type = document.querySelector('input[name="print_type"]:checked').value;
            let paper = document.querySelector('input[name="paper_size"]:checked').value;
            let bind = document.querySelector('input[name="binding"]:checked').value;
            
            let price = (type == 'Hitam Putih') ? 500 : 2000;
            let bindPrice = (bind == 'Spiral') ? 10000 : (bind == 'Hard Cover' ? 20000 : 0);
            let total = (price * qty) + bindPrice;

            document.getElementById('total-price').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('raw_total_price').value = total;
            document.getElementById('detail-text').innerText = `${type} • ${paper} • ${qty} lbr`;
        }

        // --- 3. ATK CART SYSTEM ---
        let cart = [];
        function addToCart(id, name, price, maxStock) {
            let existingItem = cart.find(item => item.id === id);
            if(existingItem) { 
                if(existingItem.qty < maxStock) { existingItem.qty++; } 
                else { alert('Stok tidak mencukupi (Maks: ' + maxStock + ')'); return; }
            } else { 
                cart.push({ id: id, name: name, price: price, qty: 1 }); 
            }
            updateCartUI();
        }

        function changeQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if(item) {
                item.qty += delta;
                if(item.qty <= 0) cart = cart.filter(i => i.id !== id);
            }
            updateCartUI();
        }

        function updateCartUI() {
            let container = document.getElementById('cart-items-container');
            if(!container) return;
            let totalDisplay = document.getElementById('cart-total-display');
            let inputData = document.getElementById('input_cart_data');
            let inputTotal = document.getElementById('input_cart_total');
            let rawTotal = document.getElementById('raw_atk_total'); 
            
            container.innerHTML = '';
            let grandTotal = 0;

            if(cart.length === 0) { container.innerHTML = '<p style="color:#999; font-size:13px; text-align:center;">Keranjang kosong.</p>'; }
            
            cart.forEach(item => {
                let subtotal = item.price * item.qty;
                grandTotal += subtotal;
                container.innerHTML += `
                    <div class="cart-item">
                        <div style="font-size:13px; font-weight:600;">${item.name}</div>
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <div class="qty-control" style="display:flex; gap:5px;">
                                <button type="button" onclick="changeQty(${item.id}, -1)">-</button>
                                <span style="font-size:13px;">${item.qty}</span>
                                <button type="button" onclick="changeQty(${item.id}, 1)">+</button>
                            </div>
                            <div style="font-size:13px;">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        </div>
                    </div>`;
            });

            totalDisplay.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            if(inputData) inputData.value = JSON.stringify(cart);
            if(inputTotal) inputTotal.value = grandTotal;
            if(rawTotal) rawTotal.value = grandTotal;
        }

        // --- 4. PAYMENT & QRIS MODAL ---
        let activeSubmitBtn = ''; 

        function checkPayment(formId, totalInputId, submitBtnId) {
            const form = document.getElementById(formId);
            let paymentName = (formId === 'formPrint') ? 'payment_method' : 'payment_method_atk';
            let paymentInput = form.querySelector(`input[name="${paymentName}"]:checked`);
            if(!paymentInput) return;

            const paymentMethod = paymentInput.value;
            const totalHarga = document.getElementById(totalInputId).value;

            if(formId === 'formATK' && totalHarga == 0) {
                alert('Keranjang masih kosong!');
                return;
            }

            if (paymentMethod === 'QRIS') {
                activeSubmitBtn = submitBtnId;
                document.getElementById('qrisModal').style.display = 'flex';
                document.getElementById('qrisTotalDisplay').innerText = 'Rp ' + parseInt(totalHarga).toLocaleString('id-ID');
            } else {
                document.getElementById(submitBtnId).click();
            }
        }

        function closeQris() { document.getElementById('qrisModal').style.display = 'none'; }
        function confirmQris() { document.getElementById(activeSubmitBtn).click(); }

        // Init
        document.addEventListener("DOMContentLoaded", function() { 
            if(document.getElementById('qty')) calcTotal(); 
        });
    </script>
</body>
</html>