<?php
include '../includes/config.php';

class ChartAnalytics {
    private $conn;
    public $currentYear;
    
    // Array Data Bulanan (0-11)
    public $monthsDataPrint;
    public $monthsDataAtk;
    public $monthsDataJilid;
    public $monthsDataRev;

    // Total Counters
    public $totalOrdersCount = 0;
    public $totalRevenueSum = 0;
    
    // Category Counters
    public $countPrint = 0;
    public $countAtk = 0;
    public $countJilid = 0;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->currentYear = date('Y');
        
        // Inisialisasi Array 12 Bulan dengan 0
        $this->monthsDataPrint = array_fill(0, 12, 0);
        $this->monthsDataAtk   = array_fill(0, 12, 0);
        $this->monthsDataJilid = array_fill(0, 12, 0);
        $this->monthsDataRev   = array_fill(0, 12, 0);
    }

    public function processData() {
        // Query ambil data tahun ini
        $query = mysqli_query($this->conn, "SELECT * FROM orders WHERE YEAR(created_at) = '$this->currentYear'");

        while($row = mysqli_fetch_assoc($query)) {
            // Ambil index bulan (0-11)
            $month_index = date('n', strtotime($row['created_at'])) - 1;
            
            // Tambah Pendapatan
            $this->monthsDataRev[$month_index] += $row['total_price'];
            $this->totalRevenueSum += $row['total_price'];
            $this->totalOrdersCount++;

            // Logika Kategori (Sama persis dengan kode asli)
            $items_lower = strtolower($row['items']);
            
            if(strpos($items_lower, 'jilid') !== false || strpos($items_lower, 'spiral') !== false || strpos($items_lower, 'hard cover') !== false) {
                $this->monthsDataJilid[$month_index]++;
                $this->countJilid++;
            } 
            elseif($row['type'] == 'ATK') {
                $this->monthsDataAtk[$month_index]++;
                $this->countAtk++;
            } 
            else {
                $this->monthsDataPrint[$month_index]++;
                $this->countPrint++;
            }
        }
    }

    // Hitung Persentase untuk Progress Bar
    public function getPercentages() {
        $safe_total = $this->totalOrdersCount > 0 ? $this->totalOrdersCount : 1;
        return [
            'print' => ($this->countPrint / $safe_total) * 100,
            'atk'   => ($this->countAtk / $safe_total) * 100,
            'jilid' => ($this->countJilid / $safe_total) * 100
        ];
    }

    // Format Data Revenue ke Juta (untuk Grafik)
    public function getRevenueInMillions() {
        return array_map(function($val) {
            return round($val / 1000000, 3);
        }, $this->monthsDataRev);
    }

    // Menentukan Kategori Terlaris
    public function getTopCategoryName() {
        if($this->countPrint >= $this->countAtk && $this->countPrint >= $this->countJilid) return "Print";
        elseif($this->countAtk >= $this->countPrint && $this->countAtk >= $this->countJilid) return "ATK";
        else return "Jilid";
    }
}

// --- EKSEKUSI PROGRAM ---

// 1. Instansiasi Class
$analytics = new ChartAnalytics($conn);

// 2. Jalankan Proses Data
$analytics->processData();

// 3. Ambil Data Tambahan (Persentase & Format Juta)
$percentages = $analytics->getPercentages();
$months_data_rev_million = $analytics->getRevenueInMillions();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan Grafik (Real Data)</title>
    <link rel="stylesheet" href="../css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="charts.php" class="active"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="manage_orders.php"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="activity_logs.php" class=><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php"><i class="ri-user-settings-line"></i> Kelola User</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <div style="margin-bottom: 15px;">
                <h4 style="margin: 0; font-weight: 600;">Admin</h4>
                <small style="opacity: 0.8;"></small>
            </div>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="page-header-row" style="border:none; padding-bottom:0;">
            <h2 style="color: var(--text-dark); font-size: 20px;">Laporan Grafik & Statistik</h2>
        </div>

        <div class="top-summary-grid">
            <div class="summary-box bg-blue-gradient">
                <i class="ri-shopping-bag-3-line summary-icon-float"></i>
                <h4 style="margin:0;">Total Pesanan</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;"><?= $analytics->totalOrdersCount ?> <small style="font-size:14px; opacity:0.8;">Trx</small></h2>
                <small>Tahun <?= $analytics->currentYear ?></small>
            </div>
            <div class="summary-box bg-green-gradient">
                <i class="ri-wallet-3-line summary-icon-float"></i>
                <h4 style="margin:0;">Pendapatan</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;">Rp <?= number_format($analytics->totalRevenueSum/1000, 0) ?>k</h2>
                <small>Total Pemasukan</small>
            </div>
            <div class="summary-box bg-purple-gradient">
                <i class="ri-pie-chart-2-line summary-icon-float"></i>
                <h4 style="margin:0;">Kategori Terlaris</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;">
                    <?= $analytics->getTopCategoryName() ?>
                </h2>
                <small>Berdasarkan Volume</small>
            </div>
        </div>

        <div class="card">
            <div class="chart-title">Grafik Pesanan Bulanan</div>
            <div class="chart-subtitle">Jumlah pesanan real-time dari database tahun <?= $analytics->currentYear ?></div>
            <div style="height: 300px;">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="chart-title">Tren Pendapatan</div>
            <div class="chart-subtitle">Akumulasi pendapatan per bulan (dalam Juta Rupiah)</div>
            <div style="height: 250px;">
                <canvas id="lineChart"></canvas>
            </div>
        </div>

        <div class="chart-row-bottom">
            <div class="card">
                <div class="chart-title">Distribusi Tipe Layanan</div>
                <div class="chart-subtitle">Persentase data pesanan</div>
                <div style="height: 250px; display:flex; justify-content:center;">
                    <canvas id="pieChart"></canvas>
                </div>
            </div>

            <div class="card">
                <div class="chart-title">Statistik Detail</div>
                <div class="chart-subtitle">Rincian volume pesanan</div>
                
                <div class="detail-item">
                    <div class="detail-label"><span>Print & Fotocopy</span> <span><?= $analytics->countPrint ?> trx</span></div>
                    <div class="detail-progress-bg"><div class="detail-progress-fill" style="width: <?= $percentages['print'] ?>%; background: #2563EB;"></div></div>
                    <small style="color:#666; font-size:12px;"><?= round($percentages['print'],1) ?>% dari total</small>
                </div>

                <div class="detail-item">
                    <div class="detail-label"><span>Produk ATK</span> <span><?= $analytics->countAtk ?> trx</span></div>
                    <div class="detail-progress-bg"><div class="detail-progress-fill" style="width: <?= $percentages['atk'] ?>%; background: #9333EA;"></div></div>
                    <small style="color:#666; font-size:12px;"><?= round($percentages['atk'],1) ?>% dari total</small>
                </div>

                <div class="detail-item">
                    <div class="detail-label"><span>Jilid & Binding</span> <span><?= $analytics->countJilid ?> trx</span></div>
                    <div class="detail-progress-bg"><div class="detail-progress-fill" style="width: <?= $percentages['jilid'] ?>%; background: #10B981;"></div></div>
                    <small style="color:#666; font-size:12px;"><?= round($percentages['jilid'],1) ?>% dari total</small>
                </div>
            </div>
        </div>

    </div>

    <script>
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

        // Ambil Data dari Property Class via JSON Encode
        const dataPrint = <?= json_encode($analytics->monthsDataPrint) ?>;
        const dataAtk   = <?= json_encode($analytics->monthsDataAtk) ?>;
        const dataJilid = <?= json_encode($analytics->monthsDataJilid) ?>;
        const dataRev   = <?= json_encode($months_data_rev_million) ?>;

        // 1. BAR CHART
        new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Print & Fotocopy', data: dataPrint, backgroundColor: '#2563EB', borderRadius: 4 },
                    { label: 'Produk ATK', data: dataAtk, backgroundColor: '#9333EA', borderRadius: 4 },
                    { label: 'Jilid & Binding', data: dataJilid, backgroundColor: '#10B981', borderRadius: 4 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
            }
        });

        // 2. LINE CHART
        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Pendapatan (Juta)',
                    data: dataRev,
                    borderColor: '#2563EB', backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 2, tension: 0.4, fill: true
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, ticks: { callback: function(val) { return val + 'jt'; } } }, 
                    x: { grid: { display: false } } 
                }
            }
        });

        // 3. PIE CHART
        new Chart(document.getElementById('pieChart'), {
            type: 'pie',
            data: {
                labels: ['Print', 'ATK', 'Jilid'],
                datasets: [{
                    data: [<?= $analytics->countPrint ?>, <?= $analytics->countAtk ?>, <?= $analytics->countJilid ?>],
                    backgroundColor: ['#2563EB', '#9333EA', '#10B981'],
                    borderWidth: 0
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    </script>
</body>
</html>