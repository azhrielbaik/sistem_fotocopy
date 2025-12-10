<?php
include '../includes/config.php';

// Class ini tetap ada untuk render awal (SSR) agar tidak kosong saat loading pertama
class ChartAnalytics {
    private $conn;
    public $currentYear;
    public $monthsDataPrint;
    public $monthsDataAtk;
    public $monthsDataJilid;
    public $monthsDataRev;
    public $totalOrdersCount = 0;
    public $totalRevenueSum = 0;
    public $countPrint = 0;
    public $countAtk = 0;
    public $countJilid = 0;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
        $this->currentYear = date('Y');
        $this->monthsDataPrint = array_fill(0, 12, 0);
        $this->monthsDataAtk   = array_fill(0, 12, 0);
        $this->monthsDataJilid = array_fill(0, 12, 0);
        $this->monthsDataRev   = array_fill(0, 12, 0);
    }

    public function processData() {
        $query = mysqli_query($this->conn, "SELECT * FROM orders WHERE YEAR(created_at) = '$this->currentYear'");
        while($row = mysqli_fetch_assoc($query)) {
            $month_index = date('n', strtotime($row['created_at'])) - 1;
            $this->monthsDataRev[$month_index] += $row['total_price'];
            $this->totalRevenueSum += $row['total_price'];
            $this->totalOrdersCount++;
            $items_lower = strtolower($row['items']);
            if(strpos($items_lower, 'jilid') !== false || strpos($items_lower, 'spiral') !== false) {
                $this->monthsDataJilid[$month_index]++; $this->countJilid++;
            } elseif($row['type'] == 'ATK') {
                $this->monthsDataAtk[$month_index]++; $this->countAtk++;
            } else {
                $this->monthsDataPrint[$month_index]++; $this->countPrint++;
            }
        }
    }

    public function getPercentages() {
        $safe_total = $this->totalOrdersCount > 0 ? $this->totalOrdersCount : 1;
        return [
            'print' => ($this->countPrint / $safe_total) * 100,
            'atk'   => ($this->countAtk / $safe_total) * 100,
            'jilid' => ($this->countJilid / $safe_total) * 100
        ];
    }

    public function getRevenueInMillions() {
        return array_map(function($val) { return round($val / 1000000, 3); }, $this->monthsDataRev);
    }

    public function getTopCategoryName() {
        if($this->countPrint >= $this->countAtk && $this->countPrint >= $this->countJilid) return "Print";
        elseif($this->countAtk >= $this->countPrint && $this->countAtk >= $this->countJilid) return "ATK";
        else return "Jilid";
    }
}

$analytics = new ChartAnalytics($conn);
$analytics->processData();
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

    <style>
        .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #E5E7EB; }
        .notification-wrapper { position: relative; cursor: pointer; margin-right: 20px; transition: transform 0.2s; }
        .notification-wrapper:hover { transform: scale(1.1); }
        .notification-icon { font-size: 26px; color: #6B7280; }
        .notification-badge { position: absolute; top: -5px; right: -5px; background-color: #EF4444; color: white; font-size: 11px; font-weight: bold; height: 18px; min-width: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid white; display: none; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); } 100% { box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); } }
    </style>
</head>
<body>

    <div class="sidebar">
        <div>
            <div class="brand-header" style="margin-bottom: 30px; display: flex; align-items: center; gap: 10px;">
                <div style="background: rgba(255,255,255,0.2); padding: 5px; border-radius: 8px;">
                    <i class="ri-printer-cloud-line" style="font-size: 28px;"></i>
                </div>
                <div><h3 style="margin:0;">Admin Panel</h3><small>Si-Foprint</small></div>
            </div>
            <ul class="menu">
                <li><a href="charts.php" class="active"><i class="ri-pie-chart-line"></i> Laporan Grafik</a></li>
                <li><a href="manage_orders.php"><i class="ri-dashboard-line"></i> Kelola Pesanan</a></li>
                <li><a href="data_pesanan.php"><i class="ri-archive-line"></i> Data Pesanan</a></li>
                <li><a href="items.php"><i class="ri-shopping-bag-3-line"></i> Data Barang ATK</a></li>
                <li><a href="activity_logs.php"><i class="ri-history-line"></i> Log Aktivitas</a></li>
                <li><a href="reviews.php"><i class="ri-star-line"></i> Ulasan User</a></li>
                <li><a href="manage_users.php"><i class="ri-user-settings-line"></i> Kelola User</a></li>
            </ul>
        </div>
        <div class="user-footer">
            <h4 style="margin:0;">Admin</h4>
            <a href="../logout.php" class="btn-logout"><i class="ri-logout-box-r-line"></i> Logout</a>
        </div>
    </div>

    <div class="main-content" style="background-color: #F9FAFB;">
        
        <div class="top-header">
            <div><h2 style="color: var(--text-dark); font-size: 20px; margin:0;">Laporan Grafik & Statistik</h2></div>
            <div class="notification-wrapper" onclick="window.location.href='manage_orders.php'" title="Lihat Pesanan Masuk">
                <i class="ri-notification-3-line notification-icon" id="notifIcon"></i>
                <div class="notification-badge" id="notifBadge">0</div>
            </div>
        </div>

        <div class="top-summary-grid">
            <div class="summary-box bg-blue-gradient">
                <i class="ri-shopping-bag-3-line summary-icon-float"></i>
                <h4 style="margin:0;">Total Pesanan</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;">
                    <span id="totalOrders"><?= $analytics->totalOrdersCount ?></span> 
                    <small style="font-size:14px; opacity:0.8;">Trx</small>
                </h2>
                <small>Tahun <?= $analytics->currentYear ?></small>
            </div>
            <div class="summary-box bg-green-gradient">
                <i class="ri-wallet-3-line summary-icon-float"></i>
                <h4 style="margin:0;">Pendapatan</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;">
                    Rp <span id="totalRevenue"><?= number_format($analytics->totalRevenueSum/1000, 0) ?></span>k
                </h2>
                <small>Total Pemasukan</small>
            </div>
            <div class="summary-box bg-purple-gradient">
                <i class="ri-pie-chart-2-line summary-icon-float"></i>
                <h4 style="margin:0;">Kategori Terlaris</h4>
                <h2 style="margin:10px 0 0; font-size: 28px;" id="topCategory">
                    <?= $analytics->getTopCategoryName() ?>
                </h2>
                <small>Berdasarkan Volume</small>
            </div>
        </div>

        <div class="card">
            <div class="chart-title">Grafik Pesanan Bulanan</div>
            <div class="chart-subtitle">Jumlah pesanan real-time dari database tahun <?= $analytics->currentYear ?></div>
            <div style="height: 300px;"><canvas id="barChart"></canvas></div>
        </div>

        <div class="card" style="margin-top: 20px;">
            <div class="chart-title">Tren Pendapatan</div>
            <div class="chart-subtitle">Akumulasi pendapatan per bulan (dalam Juta Rupiah)</div>
            <div style="height: 250px;"><canvas id="lineChart"></canvas></div>
        </div>

        <div class="chart-row-bottom">
            <div class="card">
                <div class="chart-title">Distribusi Tipe Layanan</div>
                <div class="chart-subtitle">Persentase data pesanan</div>
                <div style="height: 250px; display:flex; justify-content:center;"><canvas id="pieChart"></canvas></div>
            </div>

            <div class="card">
                <div class="chart-title">Statistik Detail</div>
                <div class="chart-subtitle">Rincian volume pesanan</div>
                
                <div class="detail-item">
                    <div class="detail-label"><span>Print & Fotocopy</span> <span><span id="countPrint"><?= $analytics->countPrint ?></span> trx</span></div>
                    <div class="detail-progress-bg"><div id="barPrint" class="detail-progress-fill" style="width: <?= $percentages['print'] ?>%; background: #2563EB;"></div></div>
                    <small style="color:#666; font-size:12px;"><span id="pctPrint"><?= round($percentages['print'],1) ?></span>% dari total</small>
                </div>

                <div class="detail-item">
                    <div class="detail-label"><span>Produk ATK</span> <span><span id="countAtk"><?= $analytics->countAtk ?></span> trx</span></div>
                    <div class="detail-progress-bg"><div id="barAtk" class="detail-progress-fill" style="width: <?= $percentages['atk'] ?>%; background: #9333EA;"></div></div>
                    <small style="color:#666; font-size:12px;"><span id="pctAtk"><?= round($percentages['atk'],1) ?></span>% dari total</small>
                </div>

                <div class="detail-item">
                    <div class="detail-label"><span>Jilid & Binding</span> <span><span id="countJilid"><?= $analytics->countJilid ?></span> trx</span></div>
                    <div class="detail-progress-bg"><div id="barJilid" class="detail-progress-fill" style="width: <?= $percentages['jilid'] ?>%; background: #10B981;"></div></div>
                    <small style="color:#666; font-size:12px;"><span id="pctJilid"><?= round($percentages['jilid'],1) ?></span>% dari total</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- 1. INISIALISASI VARIABEL GLOBAL ---
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        
        // Data Awal dari PHP (Agar tidak kosong saat load pertama)
        let chartData = {
            print: <?= json_encode($analytics->monthsDataPrint) ?>,
            atk: <?= json_encode($analytics->monthsDataAtk) ?>,
            jilid: <?= json_encode($analytics->monthsDataJilid) ?>,
            revenue: <?= json_encode($months_data_rev_million) ?>,
            pie: [<?= $analytics->countPrint ?>, <?= $analytics->countAtk ?>, <?= $analytics->countJilid ?>]
        };

        // --- 2. INISIALISASI CHARTJS INSTANCES ---
        const ctxBar = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [
                    { label: 'Print & Fotocopy', data: chartData.print, backgroundColor: '#2563EB', borderRadius: 4 },
                    { label: 'Produk ATK', data: chartData.atk, backgroundColor: '#9333EA', borderRadius: 4 },
                    { label: 'Jilid & Binding', data: chartData.jilid, backgroundColor: '#10B981', borderRadius: 4 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } }, x: { grid: { display: false } } } }
        });

        const ctxLine = document.getElementById('lineChart').getContext('2d');
        const lineChart = new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: months,
                datasets: [{ label: 'Pendapatan (Juta)', data: chartData.revenue, borderColor: '#2563EB', backgroundColor: 'rgba(37, 99, 235, 0.1)', borderWidth: 2, tension: 0.4, fill: true }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: val => val + 'jt' } }, x: { grid: { display: false } } } }
        });

        const ctxPie = document.getElementById('pieChart').getContext('2d');
        const pieChart = new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: ['Print', 'ATK', 'Jilid'],
                datasets: [{ data: chartData.pie, backgroundColor: ['#2563EB', '#9333EA', '#10B981'], borderWidth: 0 }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });

        // --- 3. FUNGSI AUTO UPDATE DATA (AJAX) ---
        function updateDashboardData() {
            // Update Notifikasi
            fetch('check_new_orders.php')
                .then(res => res.json())
                .then(data => {
                    const count = parseInt(data.pending_count);
                    const badge = document.getElementById('notifBadge');
                    const icon = document.getElementById('notifIcon');
                    if (count > 0) {
                        badge.style.display = 'flex'; badge.innerText = count > 9 ? '9+' : count;
                        icon.style.color = '#111827';
                    } else {
                        badge.style.display = 'none'; icon.style.color = '#6B7280';
                    }
                });

            // Update Grafik & Statistik
            fetch('get_chart_data.php')
                .then(res => res.json())
                .then(data => {
                    // Update Angka Summary
                    document.getElementById('totalOrders').innerText = data.total_orders;
                    document.getElementById('totalRevenue').innerText = data.total_revenue;
                    document.getElementById('topCategory').innerText = data.top_category;

                    // Update Angka Detail & Progress Bar
                    document.getElementById('countPrint').innerText = data.counts.print;
                    document.getElementById('pctPrint').innerText = data.percentages.print;
                    document.getElementById('barPrint').style.width = data.percentages.print + '%';

                    document.getElementById('countAtk').innerText = data.counts.atk;
                    document.getElementById('pctAtk').innerText = data.percentages.atk;
                    document.getElementById('barAtk').style.width = data.percentages.atk + '%';

                    document.getElementById('countJilid').innerText = data.counts.jilid;
                    document.getElementById('pctJilid').innerText = data.percentages.jilid;
                    document.getElementById('barJilid').style.width = data.percentages.jilid + '%';

                    // Update Chart Data (Tanpa Redraw Total)
                    // Bar Chart
                    barChart.data.datasets[0].data = data.chart_data.print;
                    barChart.data.datasets[1].data = data.chart_data.atk;
                    barChart.data.datasets[2].data = data.chart_data.jilid;
                    barChart.update('none'); // Mode 'none' agar animasi halus

                    // Line Chart
                    lineChart.data.datasets[0].data = data.chart_data.revenue;
                    lineChart.update('none');

                    // Pie Chart
                    pieChart.data.datasets[0].data = [data.counts.print, data.counts.atk, data.counts.jilid];
                    pieChart.update('none');
                })
                .catch(err => console.error('Error updating charts:', err));
        }

        // Jalankan setiap 3 detik
        setInterval(updateDashboardData, 3000);
    </script>
</body>
</html>