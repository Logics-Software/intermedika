<?php
class OmsetHarianController extends Controller {
    private $omsetHarianModel;

    public function __construct() {
        parent::__construct();
        $this->omsetHarianModel = new OmsetHarian();
    }

    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $startDate = $_GET['start_date'] ?? date('Y-m-d');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $export = $_GET['export'] ?? '';

        // Get kodesales filter for sales role
        $kodesales = null;
        $user = Auth::user();
        if (($user['role'] ?? '') === 'sales' && !empty($user['kodesales'])) {
            $kodesales = $user['kodesales'];
        }

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            $omset = $this->omsetHarianModel->getAll($startDate, $endDate, 1, 10000, $kodesales);
            
            if ($export === 'excel') {
                $this->exportExcel($startDate, $endDate);
            } elseif ($export === 'pdf') {
                $this->exportPDF($startDate, $endDate);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 100;

        $omset = $this->omsetHarianModel->getAll($startDate, $endDate, $page, $perPage, $kodesales);
        $total = $this->omsetHarianModel->count($startDate, $endDate, $kodesales);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $summaryBySales = $this->omsetHarianModel->getSummaryBySales($startDate, $endDate, $kodesales);

        // Calculate totals for current page
        $totals = [
            'jumlahfaktur' => 0,
            'penjualan' => 0,
            'returpenjualan' => 0,
            'penjualanbersih' => 0,
            'targetpenjualan' => 0,
            'penerimaantunai' => 0,
            'cnpenjualan' => 0,
            'pencairangiro' => 0,
            'penerimaanbersih' => 0,
            'targetpenerimaan' => 0
        ];

        foreach ($omset as $row) {
            $totals['jumlahfaktur'] += (float)($row['jumlahfaktur'] ?? 0);
            $totals['penjualan'] += (float)($row['penjualan'] ?? 0);
            $totals['returpenjualan'] += (float)($row['returpenjualan'] ?? 0);
            $totals['penjualanbersih'] += (float)($row['penjualanbersih'] ?? 0);
            $totals['targetpenjualan'] += (float)($row['targetpenjualan'] ?? 0);
            $totals['penerimaantunai'] += (float)($row['penerimaantunai'] ?? 0);
            $totals['cnpenjualan'] += (float)($row['cnpenjualan'] ?? 0);
            $totals['pencairangiro'] += (float)($row['pencairangiro'] ?? 0);
            $totals['penerimaanbersih'] += (float)($row['penerimaanbersih'] ?? 0);
            $totals['targetpenerimaan'] += (float)($row['targetpenerimaan'] ?? 0);
        }

        // Calculate percentage totals
        $totals['prosenpenjualan'] = $totals['targetpenjualan'] > 0 
            ? ($totals['penjualanbersih'] / $totals['targetpenjualan']) * 100 
            : 0;
        $totals['prosenpenerimaan'] = $totals['targetpenerimaan'] > 0 
            ? ($totals['penerimaanbersih'] / $totals['targetpenerimaan']) * 100 
            : 0;

        // Get user role for view selection
        $user = Auth::user();
        $userRole = $user['role'] ?? '';
        
        $data = [
            'omset' => $omset,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totals' => $totals,
            'summaryBySales' => $summaryBySales,
            'userRole' => $userRole,
            'omsetData' => !empty($omset) && $userRole === 'sales' ? $omset[0] : null // Single record for sales
        ];

        $this->view('laporan/omset_harian', $data);
    }

    private function exportExcel($startDate, $endDate) { // Changed signature to not need $omset
        $filename = 'Laporan_Omset_Harian_' . $startDate . '_sd_' . $endDate . '_' . date('YmdHis') . '.csv';
        
        // Fetch summary data
        $summaryBySales = $this->omsetHarianModel->getSummaryBySales($startDate, $endDate);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, ['LAPORAN OMSET HARIAN - ' . $startDate . ' s/d ' . $endDate], ';');
        fputcsv($output, []);

        // Headers matching web view
        fputcsv($output, [
            'No', 'Nama Sales', 
            'Outlet', 'Penjualan', 'Retur', 'Netto', 'Target', '%', // Penjualan Group
            'Tunai', 'CN', 'Giro', 'Bersih', 'Target', '%' // Penerimaan Group
        ], ';');

        $no = 1;
        $grandTotalFaktur = 0;
        $grandTotalPenjualan = 0;
        $grandTotalRetur = 0;
        $grandTotalPenjualanBersih = 0;
        $grandTotalTargetPenjualan = 0;
        $grandTotalPenerimaanTunai = 0;
        $grandTotalCN = 0;
        $grandTotalGiro = 0;
        $grandTotalPenerimaanBersih = 0;
        $grandTotalTargetPenerimaan = 0;

        foreach ($summaryBySales as $row) {
             $prosenPenjualan = $row['total_targetpenjualan'] > 0 ? ($row['total_penjualanbersih'] / $row['total_targetpenjualan']) * 100 : 0;
             $prosenPenerimaan = $row['total_targetpenerimaan'] > 0 ? ($row['total_penerimaanbersih'] / $row['total_targetpenerimaan']) * 100 : 0;

            // Accumulate totals
            $grandTotalFaktur += (float)($row['total_jumlahfaktur'] ?? 0);
            $grandTotalPenjualan += (float)($row['total_penjualan'] ?? 0);
            $grandTotalRetur += (float)($row['total_returpenjualan'] ?? 0);
            $grandTotalPenjualanBersih += (float)($row['total_penjualanbersih'] ?? 0);
            $grandTotalTargetPenjualan += (float)($row['total_targetpenjualan'] ?? 0);
            $grandTotalPenerimaanTunai += (float)($row['total_penerimaantunai'] ?? 0);
            $grandTotalCN += (float)($row['total_cnpenjualan'] ?? 0);
            $grandTotalGiro += (float)($row['total_pencairangiro'] ?? 0);
            $grandTotalPenerimaanBersih += (float)($row['total_penerimaanbersih'] ?? 0);
            $grandTotalTargetPenerimaan += (float)($row['total_targetpenerimaan'] ?? 0);

            fputcsv($output, [
                $no++,
                $row['namasales'] ?? '',
                $row['total_jumlahfaktur'] ?? 0,
                $row['total_penjualan'] ?? 0,
                $row['total_returpenjualan'] ?? 0,
                $row['total_penjualanbersih'] ?? 0,
                $row['total_targetpenjualan'] ?? 0,
                number_format($prosenPenjualan, 2, ',', '.'),
                $row['total_penerimaantunai'] ?? 0,
                $row['total_cnpenjualan'] ?? 0,
                $row['total_pencairangiro'] ?? 0,
                $row['total_penerimaanbersih'] ?? 0,
                $row['total_targetpenerimaan'] ?? 0,
                number_format($prosenPenerimaan, 2, ',', '.')
            ], ';');
        }

        // Total Row
        $totalProsenPenjualan = $grandTotalTargetPenjualan > 0 ? ($grandTotalPenjualanBersih / $grandTotalTargetPenjualan) * 100 : 0;
        $totalProsenPenerimaan = $grandTotalTargetPenerimaan > 0 ? ($grandTotalPenerimaanBersih / $grandTotalTargetPenerimaan) * 100 : 0;

        fputcsv($output, [
            '', 'TOTAL',
            $grandTotalFaktur,
            $grandTotalPenjualan,
            $grandTotalRetur,
            $grandTotalPenjualanBersih,
            $grandTotalTargetPenjualan,
            number_format($totalProsenPenjualan, 2, ',', '.'),
            $grandTotalPenerimaanTunai,
            $grandTotalCN,
            $grandTotalGiro,
            $grandTotalPenerimaanBersih,
            $grandTotalTargetPenerimaan,
            number_format($totalProsenPenerimaan, 2, ',', '.')
        ], ';');

        fclose($output);
    }

    private function exportPDF($startDate, $endDate) { // Removed $omset arg
        $this->generateAndDownloadPDFOmset($startDate, $endDate);
    }

    private function generateAndDownloadPDFOmset($startDate, $endDate) {
        $filename = 'Omset_Penjualan_Harian_' . $startDate . '_sd_' . $endDate . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Fetch summary data
        $summaryBySales = $this->omsetHarianModel->getSummaryBySales($startDate, $endDate);

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Omset Penjualan Harian</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            margin: 15px;
        }
        h1 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 16pt;
            color: #333;
        }
        .header-info {
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .header-info p {
            margin: 5px 0;
            font-size: 10pt;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 7pt;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
        }
        th {
            background-color: #343a40;
            color: #fff;
            font-weight: bold;
        }
        td {
            background-color: #fff;
        }
        td.text-left {
            text-align: left;
        }
        tr:nth-child(even) td {
            background-color: #f8f9fa;
        }
        tr.total-row {
            background-color: #fff3cd;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 9pt;
            color: #666;
        }
        .footer p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>📊 Laporan Omset Penjualan Harian</h1>
    <div class="header-info">
        <p><strong>Periode:</strong> ' . $startDate . ' s/d ' . $endDate . '</p>
        <p><strong>Tanggal Laporan:</strong> ' . date('d F Y, H:i:s') . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 15%;">Nama Sales</th>
                <th colspan="6">Penjualan</th>
                <th colspan="6">Penerimaan</th>
            </tr>
            <tr>
                <th>Outlet</th>
                <th>Penjualan</th>
                <th>Retur</th>
                <th>Netto</th>
                <th>Target</th>
                <th>%</th>
                <th>Tunai</th>
                <th>CN</th>
                <th>Giro</th>
                <th>Bersih</th>
                <th>Target</th>
                <th>%</th>
            </tr>
        </thead>
        <tbody>';
        
        $no = 1;
        $grandTotalFaktur = 0;
        $grandTotalPenjualan = 0;
        $grandTotalRetur = 0;
        $grandTotalPenjualanBersih = 0;
        $grandTotalTargetPenjualan = 0;
        $grandTotalPenerimaanTunai = 0;
        $grandTotalCN = 0;
        $grandTotalGiro = 0;
        $grandTotalPenerimaanBersih = 0;
        $grandTotalTargetPenerimaan = 0;

        foreach ($summaryBySales as $row) {
             $prosenPenjualan = $row['total_targetpenjualan'] > 0 ? ($row['total_penjualanbersih'] / $row['total_targetpenjualan']) * 100 : 0;
             $prosenPenerimaan = $row['total_targetpenerimaan'] > 0 ? ($row['total_penerimaanbersih'] / $row['total_targetpenerimaan']) * 100 : 0;

             // Accumulate totals
            $grandTotalFaktur += (float)($row['total_jumlahfaktur'] ?? 0);
            $grandTotalPenjualan += (float)($row['total_penjualan'] ?? 0);
            $grandTotalRetur += (float)($row['total_returpenjualan'] ?? 0);
            $grandTotalPenjualanBersih += (float)($row['total_penjualanbersih'] ?? 0);
            $grandTotalTargetPenjualan += (float)($row['total_targetpenjualan'] ?? 0);
            $grandTotalPenerimaanTunai += (float)($row['total_penerimaantunai'] ?? 0);
            $grandTotalCN += (float)($row['total_cnpenjualan'] ?? 0);
            $grandTotalGiro += (float)($row['total_pencairangiro'] ?? 0);
            $grandTotalPenerimaanBersih += (float)($row['total_penerimaanbersih'] ?? 0);
            $grandTotalTargetPenerimaan += (float)($row['total_targetpenerimaan'] ?? 0);

            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td class="text-left">' . htmlspecialchars($row['namasales'] ?? '-') . '</td>
                <td>' . number_format((float)($row['total_jumlahfaktur'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_penjualan'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_returpenjualan'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_penjualanbersih'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_targetpenjualan'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format($prosenPenjualan, 2, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_penerimaantunai'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_cnpenjualan'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_pencairangiro'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_penerimaanbersih'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format((float)($row['total_targetpenerimaan'] ?? 0), 0, ',', '.') . '</td>
                <td>' . number_format($prosenPenerimaan, 2, ',', '.') . '</td>
            </tr>';
        }

        // Total Row
        $totalProsenPenjualan = $grandTotalTargetPenjualan > 0 ? ($grandTotalPenjualanBersih / $grandTotalTargetPenjualan) * 100 : 0;
        $totalProsenPenerimaan = $grandTotalTargetPenerimaan > 0 ? ($grandTotalPenerimaanBersih / $grandTotalTargetPenerimaan) * 100 : 0;

        $html .= '<tr class="total-row">
            <td colspan="2" style="text-align: center;">TOTAL</td>
            <td>' . number_format($grandTotalFaktur, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalPenjualan, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalRetur, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalPenjualanBersih, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalTargetPenjualan, 0, ',', '.') . '</td>
            <td>' . number_format($totalProsenPenjualan, 2, ',', '.') . '</td>
            <td>' . number_format($grandTotalPenerimaanTunai, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalCN, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalGiro, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalPenerimaanBersih, 0, ',', '.') . '</td>
            <td>' . number_format($grandTotalTargetPenerimaan, 0, ',', '.') . '</td>
            <td>' . number_format($totalProsenPenerimaan, 2, ',', '.') . '</td>
        </tr>'; // Note: I removed the original detail loop because the request implies matching the summary view


        $html .= '</tbody>
    </table>
    <div class="footer">
        <p><strong>Dicetak oleh:</strong> ' . htmlspecialchars(Auth::user()['namalengkap'] ?? 'System') . '</p>
        <p><strong>Tanggal:</strong> ' . date('d F Y, H:i:s') . '</p>
    </div>
</body>
</html>';

        $this->downloadAsHTML($html, $filename);
    }

    private function downloadAsHTML($html, $filename) {
        header('Content-Type: text/html; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.html"');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $html;
    }
}
