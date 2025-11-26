<?php
include '../includes/config.php';

// --- LOGIC 1: SIMPAN PESANAN PRINT ---
if(isset($_POST['submit_print'])) {
    $order_id = "ORD-" . rand(1000,9999);
    $jenis = $_POST['print_type'];
    $kertas = $_POST['paper_size'];
    $jilid = $_POST['binding'];
    $qty = $_POST['qty'];
    $catatan = $_POST['notes'];
    
    // Hitung server side
    $harga_lembar = ($jenis == 'Hitam Putih') ? 500 : 2000;
    $harga_jilid = ($jilid == 'Spiral') ? 10000 : (($jilid == 'Hard Cover') ? 20000 : 0);
    $total = ($harga_lembar * $qty) + $harga_jilid;
    
    $detail = "Print: $jenis ($kertas), Jilid: $jilid. Note: $catatan";
    
    mysqli_query($conn, "INSERT INTO orders (id, user_id, type, items, total_price, status, created_at) 
                         VALUES ('$order_id', 1, 'Print', '$detail', '$total', 'Pending', NOW())");
    echo "<script>alert('Pesanan Print Berhasil!'); window.location='view_orders.php';</script>";
}

// --- LOGIC 2: SIMPAN PESANAN ATK ---
if(isset($_POST['submit_atk'])) {
    $order_id = "ORD-" . rand(1000,9999);
    $items_json = $_POST['cart_data']; // Data JSON dari JS
    $total = $_POST['cart_total'];
    
    // Konversi JSON ke String yang enak dibaca database
    $cart_array = json_decode($items_json, true);
    $detail_str = "";
    foreach($cart_array as $item) {
        $detail_str .= $item['name'] . " (" . $item['qty'] . "x), ";
    }
    $detail_str = rtrim($detail_str, ", "); // Hapus koma terakhir

    if(!empty($detail_str)) {
        mysqli_query($conn, "INSERT INTO orders (id, user_id, type, items, total_price, status, created_at) 
                             VALUES ('$order_id', 1, 'ATK', '$detail_str', '$total', 'Pending', NOW())");
        echo "<script>alert('Pesanan ATK Berhasil!'); window.location='view_orders.php';</script>";
    } else {
        echo "<script>alert('Keranjang kosong!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Buat Pesanan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <i class="ri-printer-cloud-fill" style="font-size: 32px;"></i>
                <div>
                    <h3 style="margin: 0; font-size: 18px;">Dashboard</h3>
                    <small style="opacity: 0.8; font-size: 12px;">PrintCopy Pro</small>
                </div>
            </div>
            <ul class="menu">
                <li><a href="dashboard.php"><i class="ri-home-4-line"></i> Beranda</a></li>
                <li><a href="place_order.php" class="active"><i class="ri-shopping-cart-2-line"></i> Buat Pesanan</a></li>
                <li><a href="view_orders.php"><i class="ri-file-list-3-line"></i> Status Pesanan</a></li>
                <li><a href="rate_order.php"><i class="ri-star-line"></i> Beri Rating</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">User Demo</h4>
                <small style="opacity: 0.8;">user@example.com</small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="header" style="justify-content: center; position: relative;">
            <a href="dashboard.php" style="position: absolute; left: 0; color: #333; text-decoration: none;">
                <i class="ri-close-line" style="font-size: 24px;"></i>
            </a>
            <h4 style="color: var(--primary);">Buat Pesanan</h4>
        </div>

        <div class="tab-container">
            <button class="tab-btn active" onclick="switchTab('print')">
                <i class="ri-printer-line"></i> Print & Fotocopy
            </button>
            <button class="tab-btn" onclick="switchTab('atk')">
                <i class="ri-box-3-line"></i> Produk ATK
            </button>
        </div>

        <div id="tab-print" class="tab-content active">
            <div class="card" style="margin-top: 20px; border: none; box-shadow: none;">
                <form method="POST">
                    <label class="form-label">Jenis Print</label>
                    <div class="selection-grid">
                        <label class="selection-box selected" onclick="selectBox(this)">
                            <input type="radio" name="print_type" value="Hitam Putih" checked onchange="calcTotal()">
                            <strong>Hitam Putih</strong><br><small>Rp 500/lembar</small>
                        </label>
                        <label class="selection-box" onclick="selectBox(this)">
                            <input type="radio" name="print_type" value="Berwarna" onchange="calcTotal()">
                            <strong>Berwarna</strong><br><small>Rp 2.000/lembar</small>
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
                    <input type="number" id="qty" name="qty" class="form-control bg-gray" value="1" min="1" oninput="calcTotal()">

                    <label class="form-label">Jilid (Opsional)</label>
                    <div class="selection-grid" style="grid-template-columns: 1fr 1fr 1fr;">
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
                    <textarea name="notes" class="form-control bg-gray" rows="3"></textarea>

                    <div class="estimate-box">
                        <div><strong>Total Estimasi:</strong><br><span id="detail-text" style="font-size: 13px;">Hitam Putih • 1 lembar</span></div>
                        <div id="total-price" style="font-size: 18px; font-weight: bold;">Rp 500</div>
                    </div>

                    <button type="submit" name="submit_print" class="btn btn-full">Buat Pesanan Print</button>
                </form>
            </div>
        </div>

        <div id="tab-atk" class="tab-content">
            
            <div class="atk-layout">
                <div class="product-grid">
                    <?php
                    // Ambil data produk dari database 'items'
                    $products = mysqli_query($conn, "SELECT * FROM items");
                    while($p = mysqli_fetch_assoc($products)):
                    ?>
                    <div class="product-card">
                        <i class="ri-box-3-fill product-icon" style="font-size: 32px;"></i>
                        <h4 style="margin: 0; font-size: 14px;"><?= $p['name'] ?></h4>
                        <span class="badge bg-pending" style="font-size: 10px; margin: 5px 0; display:inline-block;">Stok: <?= $p['stock'] ?></span>
                        <div style="font-weight: bold; color: var(--primary); margin-top: 5px;"><?= formatRupiah($p['price']) ?></div>
                        
                        <button class="btn-add" onclick="addToCart(<?= $p['id'] ?>, '<?= $p['name'] ?>', <?= $p['price'] ?>)">
                            <i class="ri-add-line"></i> Tambah
                        </button>
                    </div>
                    <?php endwhile; ?>
                </div>

                <div class="cart-sidebar">
                    <h3 style="display:flex; align-items:center; gap:10px; font-size: 16px; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:15px;">
                        <i class="ri-shopping-cart-line"></i> Keranjang
                    </h3>
                    
                    <div id="cart-items-container">
                        <p style="color: #999; font-size: 13px; text-align: center;">Belum ada item dipilih.</p>
                    </div>

                    <div style="border-top:1px solid #eee; padding-top:15px; margin-top:15px;">
                        <div style="display:flex; justify-content:space-between; font-weight:bold; margin-bottom:15px;">
                            <span>Total:</span>
                            <span id="cart-total-display">Rp 0</span>
                        </div>
                        
                        <form method="POST">
                            <input type="hidden" name="cart_data" id="input_cart_data">
                            <input type="hidden" name="cart_total" id="input_cart_total">
                            <button type="submit" name="submit_atk" class="btn btn-full">Checkout</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        // --- LOGIC TAB SWITCHING ---
        function switchTab(tabName) {
            // Hide all
            document.getElementById('tab-print').classList.remove('active');
            document.getElementById('tab-atk').classList.remove('active');
            
            // Remove active button style
            let buttons = document.querySelectorAll('.tab-btn');
            buttons.forEach(btn => btn.classList.remove('active'));

            // Show selected
            document.getElementById('tab-' + tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // --- LOGIC PRINT FORM (Sama kayak sebelumnya) ---
        function selectBox(el) {
            let siblings = el.parentElement.children;
            for(let sib of siblings) sib.classList.remove('selected');
            el.classList.add('selected');
            calcTotal();
        }

        function calcTotal() {
            let qty = document.getElementById('qty').value;
            let type = document.querySelector('input[name="print_type"]:checked').value;
            let paper = document.querySelector('input[name="paper_size"]:checked').value;
            let bind = document.querySelector('input[name="binding"]:checked').value;
            
            let price = (type == 'Hitam Putih') ? 500 : 2000;
            let bindPrice = (bind == 'Spiral') ? 10000 : (bind == 'Hard Cover' ? 20000 : 0);
            let total = (price * qty) + bindPrice;

            document.getElementById('total-price').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('detail-text').innerText = `${type} • ${paper} • ${qty} lbr`;
        }

        // --- LOGIC KERANJANG ATK (BARU) ---
        let cart = [];

        function addToCart(id, name, price) {
            // Cek jika item sudah ada
            let existingItem = cart.find(item => item.id === id);
            
            if(existingItem) {
                existingItem.qty++;
            } else {
                cart.push({ id: id, name: name, price: price, qty: 1 });
            }
            updateCartUI();
        }

        function changeQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if(item) {
                item.qty += delta;
                if(item.qty <= 0) {
                    cart = cart.filter(i => i.id !== id); // Hapus jika 0
                }
            }
            updateCartUI();
        }

        function updateCartUI() {
            let container = document.getElementById('cart-items-container');
            let totalDisplay = document.getElementById('cart-total-display');
            let inputData = document.getElementById('input_cart_data');
            let inputTotal = document.getElementById('input_cart_total');
            
            container.innerHTML = '';
            let grandTotal = 0;

            if(cart.length === 0) {
                container.innerHTML = '<p style="color: #999; font-size: 13px; text-align: center;">Belum ada item dipilih.</p>';
            }

            cart.forEach(item => {
                let subtotal = item.price * item.qty;
                grandTotal += subtotal;

                let html = `
                    <div class="cart-item">
                        <div style="font-size:13px; font-weight:600;">${item.name}</div>
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <div class="qty-control">
                                <button type="button" class="btn-qty" onclick="changeQty(${item.id}, -1)">-</button>
                                <span style="font-size:13px;">${item.qty}</span>
                                <button type="button" class="btn-qty" onclick="changeQty(${item.id}, 1)">+</button>
                            </div>
                            <div style="font-size:13px;">Rp ${subtotal.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                `;
                container.innerHTML += html;
            });

            // Update Total & Input Hidden
            totalDisplay.innerText = 'Rp ' + grandTotal.toLocaleString('id-ID');
            inputData.value = JSON.stringify(cart);
            inputTotal.value = grandTotal;
        }
        
        // Init logic print saat load
        document.addEventListener("DOMContentLoaded", function() { calcTotal(); });
    </script>
</body>
</html>