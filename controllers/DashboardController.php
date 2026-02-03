<?php
class DashboardController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $role = $user['role'] ?? '';
        
        $data = [
            'user' => $user,
            'role' => $role
        ];
        
        // Get statistics based on role
        if ($role === 'admin' || $role === 'manajemen') {
            // Admin & Manajemen: Full statistics
            $data['stats'] = $this->getAdminStats();
        } elseif ($role === 'operator') {
            // Operator: Operational statistics
            $data['stats'] = $this->getOperatorStats();
        } elseif ($role === 'sales') {
            // Sales: Personal sales statistics
            $data['stats'] = $this->getSalesStats($user['kodesales'] ?? null);
        } else {
            $data['stats'] = [];
        }
        
        $this->view('dashboard/index', $data);
    }
    
    private function getAdminStats() {
        $headerOrder = new Headerorder();
        $headerPenjualan = new Headerpenjualan();
        $headerPenerimaan = new Headerpenerimaan();
        $userModel = new User();
        $messageModel = new MessageModel();
        $loginLogModel = new LoginLog();
        
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        
        // Options for all sales (no kodesales filter)
        $options = [
            'start_date' => $today,
            'end_date' => $today
        ];
        
        // Get monthly sales data for YTD chart (all sales)
        $monthlySales = $this->getMonthlySalesYTD(null);
        // Get monthly inkaso data for YTD chart (all sales)
        $monthlyInkaso = $this->getMonthlyInkasoYTD(null);
        // Get recent price changes
        $priceChanges = $this->getRecentPriceChanges();
        // Get overdue invoices (all sales)
        $overdueInvoices = $this->getOverdueInvoices(null);
        
        return [
            'total_orders' => $headerOrder->count($options),
            'total_penjualan' => $headerPenjualan->count(array_merge($options, ['periode' => 'today'])),
            'total_penerimaan' => $headerPenerimaan->count($options),
            'total_users' => $userModel->count(),
            'total_user_logs_today' => $loginLogModel->countToday(),
            'unread_messages' => $messageModel->getUnreadCount(Auth::user()['id'] ?? 0),
            // Add totals and charts for all sales
            'all_orders_total' => $headerOrder->sumTotal($options),
            'all_penjualan_total' => $headerPenjualan->getTotals(array_merge($options, ['periode' => 'today']))['nilaipenjualan'],
            'all_penerimaan_total' => $headerPenerimaan->sumTotal($options),
            'monthly_sales' => $monthlySales,
            'monthly_inkaso' => $monthlyInkaso,
            'price_changes' => $priceChanges,
            'overdue_invoices' => $overdueInvoices
        ];
    }
    
    private function getOperatorStats() {
        $headerOrder = new Headerorder();
        $headerPenjualan = new Headerpenjualan();
        $headerPenerimaan = new Headerpenerimaan();
        $messageModel = new MessageModel();
        
        $today = date('Y-m-d');
        
        // Options for all sales (no kodesales filter)
        $options = [
            'start_date' => $today,
            'end_date' => $today
        ];
        
        // Get recent price changes
        $priceChanges = $this->getRecentPriceChanges();
        // Get overdue invoices (all sales)
        $overdueInvoices = $this->getOverdueInvoices(null);
        
        return [
            'total_orders' => $headerOrder->count($options),
            'total_penjualan' => $headerPenjualan->count(array_merge($options, ['periode' => 'today'])),
            'total_penerimaan' => $headerPenerimaan->count($options),
            'unread_messages' => $messageModel->getUnreadCount(Auth::user()['id'] ?? 0),
            // Add totals for all sales (same as admin)
            'all_orders_total' => $headerOrder->sumTotal($options),
            'all_penjualan_total' => $headerPenjualan->getTotals(array_merge($options, ['periode' => 'today']))['nilaipenjualan'],
            'all_penerimaan_total' => $headerPenerimaan->sumTotal($options),
            // Add price changes and overdue invoices
            'price_changes' => $priceChanges,
            'overdue_invoices' => $overdueInvoices
        ];
    }
    
    private function getSalesStats($kodesales) {
        $headerOrder = new Headerorder();
        $headerPenjualan = new Headerpenjualan();
        $headerPenerimaan = new Headerpenerimaan();
        $messageModel = new MessageModel();
        
        $today = date('Y-m-d');
        $options = [
            'start_date' => $today,
            'end_date' => $today
        ];
        
        if ($kodesales) {
            $options['kodesales'] = $kodesales;
        }
        
        // Get monthly sales data for YTD chart
        $monthlySales = $this->getMonthlySalesYTD($kodesales);
        // Get monthly inkaso data for YTD chart
        $monthlyInkaso = $this->getMonthlyInkasoYTD($kodesales);
        // Get recent price changes
        $priceChanges = $this->getRecentPriceChanges();
        // Get overdue invoices
        $overdueInvoices = $this->getOverdueInvoices($kodesales);
        // Get recent barang datang
        $barangDatang = $this->getRecentBarangDatang();
        
        return [
            'my_orders' => $headerOrder->count($options),
            'my_penjualan' => $headerPenjualan->count(array_merge($options, ['periode' => 'today'])),
            'my_penerimaan' => $headerPenerimaan->count($options),
            'my_orders_total' => $headerOrder->sumTotal($options),
            'my_penjualan_total' => $headerPenjualan->getTotals(array_merge($options, ['periode' => 'today']))['nilaipenjualan'],
            'my_penerimaan_total' => $headerPenerimaan->sumTotal($options),
            'monthly_sales' => $monthlySales,
            'monthly_inkaso' => $monthlyInkaso,
            'price_changes' => $priceChanges,
            'overdue_invoices' => $overdueInvoices,
            'barang_datang' => $barangDatang,
            'unread_messages' => $messageModel->getUnreadCount(Auth::user()['id'] ?? 0)
        ];
    }
    
    private function getMonthlySalesYTD($kodesales) {
        $db = Database::getInstance();
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        // Calculate start date: 12 months ago from current month
        // If current month is November, start from December last year
        $startMonth = $currentMonth + 1; // Next month (December if November)
        if ($startMonth > 12) {
            $startMonth = 1;
            $startYear = $currentYear; // If December, start from January same year
        } else {
            $startYear = $currentYear - 1; // Otherwise, start from last year
        }
        
        // Build month sequence: 12 months from start month to current month
        $monthlyData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        $monthIndex = $startMonth - 1; // 0-based index
        $year = $startYear;
        
        for ($i = 0; $i < 12; $i++) {
            $monthNum = $monthIndex + 1;
            $monthlyData[] = [
                'month' => $monthNames[$monthIndex],
                'year' => $year,
                'monthNum' => $monthNum,
                'total' => 0
            ];
            
            $monthIndex++;
            if ($monthIndex >= 12) {
                $monthIndex = 0;
                $year++;
            }
        }
        
        // Build WHERE clause for omset table
        $whereConditions = [];
        $params = [];
        
        foreach ($monthlyData as $monthData) {
            $whereConditions[] = "(tahun = ? AND bulan = ?)";
            $params[] = $monthData['year'];
            $params[] = $monthData['monthNum'];
        }
        
        $where = "(" . implode(' OR ', $whereConditions) . ")";
        
        if ($kodesales) {
            $where .= " AND kodesales = ?";
            $params[] = $kodesales;
        }
        
        // Get monthly totals from omset table
        $sql = "SELECT 
                    tahun,
                    bulan,
                    COALESCE(SUM(penjualanbersih), 0) as total
                FROM omset
                WHERE {$where}
                GROUP BY tahun, bulan
                ORDER BY tahun, bulan";
        
        $results = $db->fetchAll($sql, $params);
        
        // Fill in actual data
        foreach ($results as $row) {
            // Find matching month in our sequence
            foreach ($monthlyData as &$monthData) {
                if ($monthData['year'] == $row['tahun'] && $monthData['monthNum'] == $row['bulan']) {
                    $monthData['total'] = (float)$row['total'];
                    break;
                }
            }
            unset($monthData);
        }
        
        // Return only the month names and totals for chart
        $chartData = [];
        foreach ($monthlyData as $data) {
            $chartData[] = [
                'month' => $data['month'],
                'total' => $data['total']
            ];
        }
        
        return $chartData;
    }
    
    private function getMonthlyInkasoYTD($kodesales) {
        $db = Database::getInstance();
        
        $currentYear = (int)date('Y');
        $currentMonth = (int)date('m');
        
        // Calculate start date: 12 months ago from current month
        // If current month is November, start from December last year
        $startMonth = $currentMonth + 1; // Next month (December if November)
        if ($startMonth > 12) {
            $startMonth = 1;
            $startYear = $currentYear; // If December, start from January same year
        } else {
            $startYear = $currentYear - 1; // Otherwise, start from last year
        }
        
        // Build month sequence: 12 months from start month to current month
        $monthlyData = [];
        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        $monthIndex = $startMonth - 1; // 0-based index
        $year = $startYear;
        
        for ($i = 0; $i < 12; $i++) {
            $monthNum = $monthIndex + 1;
            $monthlyData[] = [
                'month' => $monthNames[$monthIndex],
                'year' => $year,
                'monthNum' => $monthNum,
                'total' => 0
            ];
            
            $monthIndex++;
            if ($monthIndex >= 12) {
                $monthIndex = 0;
                $year++;
            }
        }
        
        // Build WHERE clause for omset table
        $whereConditions = [];
        $params = [];
        
        foreach ($monthlyData as $monthData) {
            $whereConditions[] = "(tahun = ? AND bulan = ?)";
            $params[] = $monthData['year'];
            $params[] = $monthData['monthNum'];
        }
        
        $where = "(" . implode(' OR ', $whereConditions) . ")";
        
        if ($kodesales) {
            $where .= " AND kodesales = ?";
            $params[] = $kodesales;
        }
        
        // Get monthly totals from omset table
        $sql = "SELECT 
                    tahun,
                    bulan,
                    COALESCE(SUM(penerimaanbersih), 0) as total
                FROM omset
                WHERE {$where}
                GROUP BY tahun, bulan
                ORDER BY tahun, bulan";
        
        $results = $db->fetchAll($sql, $params);
        
        // Fill in actual data
        foreach ($results as $row) {
            // Find matching month in our sequence
            foreach ($monthlyData as &$monthData) {
                if ($monthData['year'] == $row['tahun'] && $monthData['monthNum'] == $row['bulan']) {
                    $monthData['total'] = (float)$row['total'];
                    break;
                }
            }
            unset($monthData);
        }
        
        // Return only the month names and totals for chart
        $chartData = [];
        foreach ($monthlyData as $data) {
            $chartData[] = [
                'month' => $data['month'],
                'total' => $data['total']
            ];
        }
        
        return $chartData;
    }
    
    private function getRecentPriceChanges() {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik as pabrik,
                    mb.kondisi,
                    mb.ed,
                    ph.hargabaru as harga,
                    ph.discountbaru as discount
                FROM perubahanharga ph
                LEFT JOIN masterbarang mb ON ph.kodebarang = mb.kodebarang
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                ORDER BY ph.tanggalperubahan DESC, ph.id DESC
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
    
    private function getOverdueInvoices($kodesales) {
        $db = Database::getInstance();
        $today = date('Y-m-d');
        
        $where = ["hp.tanggaljatuhtempo < ? AND hp.saldopenjualan > 0"];
        $params = [$today];
        
        // Always filter by kodesales if provided (required for sales role)
        if ($kodesales) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT 
                    hp.nopenjualan,
                    hp.tanggalpenjualan,
                    hp.tanggaljatuhtempo,
                    hp.saldopenjualan,
                    mc.namacustomer,
                    mc.alamatcustomer,
                    DATEDIFF(?, hp.tanggalpenjualan) as umur
                FROM headerpenjualan hp
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}
                ORDER BY umur DESC
                LIMIT 10";
        
        // Add today as first parameter for DATEDIFF
        array_unshift($params, $today);
        
        return $db->fetchAll($sql, $params);
    }
    
    private function getRecentBarangDatang() {
        $db = Database::getInstance();
        
        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik as pabrik,
                    mb.kondisi,
                    mb.ed,
                    pb.tanggalpembelian as tanggal,
                    pb.jumlah
                FROM pembelianbarang pb
                LEFT JOIN masterbarang mb ON pb.kodebarang = mb.kodebarang
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                ORDER BY pb.tanggalpembelian DESC
                LIMIT 10";
        
        return $db->fetchAll($sql);
    }
}

