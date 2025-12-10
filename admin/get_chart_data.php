<?php
// admin/get_chart_data.php
include '../includes/config.php';

class ChartDataAPI {
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

    public function getResponse() {
        $safe_total = $this->totalOrdersCount > 0 ? $this->totalOrdersCount : 1;
        
        // Kategori Terlaris
        $topCategory = "Print";
        if($this->countAtk >= $this->countPrint && $this->countAtk >= $this->countJilid) $topCategory = "ATK";
        elseif($this->countJilid >= $this->countPrint && $this->countJilid >= $this->countAtk) $topCategory = "Jilid";

        // Revenue in Millions
        $revMillion = array_map(function($val) { return round($val / 1000000, 3); }, $this->monthsDataRev);

        return [
            'total_orders' => $this->totalOrdersCount,
            'total_revenue' => number_format($this->totalRevenueSum/1000, 0),
            'top_category' => $topCategory,
            'counts' => [
                'print' => $this->countPrint,
                'atk' => $this->countAtk,
                'jilid' => $this->countJilid
            ],
            'percentages' => [
                'print' => round(($this->countPrint / $safe_total) * 100, 1),
                'atk' => round(($this->countAtk / $safe_total) * 100, 1),
                'jilid' => round(($this->countJilid / $safe_total) * 100, 1)
            ],
            'chart_data' => [
                'print' => $this->monthsDataPrint,
                'atk' => $this->monthsDataAtk,
                'jilid' => $this->monthsDataJilid,
                'revenue' => $revMillion
            ]
        ];
    }
}

$api = new ChartDataAPI($conn);
$api->processData();

header('Content-Type: application/json');
echo json_encode($api->getResponse());
?>