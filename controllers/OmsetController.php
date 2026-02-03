<?php
class OmsetController extends Controller {
    private $omsetModel;

    public function __construct() {
        parent::__construct();
        $this->omsetModel = new Omset();
    }

    public function index() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $tahun = $_GET['tahun'] ?? date('Y');
        $bulan = $_GET['bulan'] ?? date('m');
        $export = $_GET['export'] ?? '';

        // Get kodesales filter for sales role
        $kodesales = null;
        $user = Auth::user();
        if (($user['role'] ?? '') === 'sales' && !empty($user['kodesales'])) {
            $kodesales = $user['kodesales'];
        }

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            $omset = $this->omsetModel->getAll($tahun, $bulan, 1, 10000, $kodesales);
            
            if ($export === 'excel') {
                $this->exportExcel($omset, $tahun, $bulan);
            } elseif ($export === 'pdf') {
                $this->exportPDF($omset, $tahun, $bulan);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 100;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 100;

        $omset = $this->omsetModel->getAll($tahun, $bulan, $page, $perPage, $kodesales);
        $total = $this->omsetModel->count($tahun, $bulan, $kodesales);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

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

        // Get distinct years for dropdown
        $years = $this->omsetModel->getDistinctYears();
        if (empty($years)) {
            $years = [date('Y')];
        }

        // Get user role for view selection
        $user = Auth::user();
        $userRole = $user['role'] ?? '';
        
        $data = [
            'omset' => $omset,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'years' => $years,
            'totals' => $totals,
            'userRole' => $userRole,
            'omsetData' => !empty($omset) && $userRole === 'sales' ? $omset[0] : null // Single record for sales
        ];

        $this->view('laporan/omset', $data);
    }

    private function exportExcel($omset, $tahun, $bulan) {
        $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanText = isset($bulanNama[(int)$bulan]) ? $bulanNama[(int)$bulan] : $bulan;
        
        $filename = 'Laporan_Omset_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, ['LAPORAN OMSET - ' . $bulanText . ' ' . $tahun], ';');
        fputcsv($output, []);

        fputcsv($output, [
            'Tahun', 'Bulan', 'Kode Sales', 'Nama Sales', 'Jumlah Faktur', 'Penjualan',
            'Retur Penjualan', 'Penjualan Bersih', 'Target Penjualan', 'Prosen Penjualan',
            'Penerimaan Tunai', 'CN Penjualan', 'Pencairan Giro', 'Penerimaan Bersih',
            'Target Penerimaan', 'Prosen Penerimaan'
        ], ';');

        foreach ($omset as $row) {
            fputcsv($output, [
                $row['tahun'] ?? '',
                $row['bulan'] ?? '',
                $row['kodesales'] ?? '',
                $row['namasales'] ?? '',
                $row['jumlahfaktur'] ?? 0,
                $row['penjualan'] ?? 0,
                $row['returpenjualan'] ?? 0,
                $row['penjualanbersih'] ?? 0,
                $row['targetpenjualan'] ?? 0,
                $row['prosenpenjualan'] ?? 0,
                $row['penerimaantunai'] ?? 0,
                $row['cnpenjualan'] ?? 0,
                $row['pencairangiro'] ?? 0,
                $row['penerimaanbersih'] ?? 0,
                $row['targetpenerimaan'] ?? 0,
                $row['prosenpenerimaan'] ?? 0
            ], ';');
        }

        fclose($output);
    }

    private function exportPDF($omset, $tahun, $bulan) {
        $this->generateAndDownloadPDFOmset($omset, $tahun, $bulan);
    }

    private function generateAndDownloadPDFOmset($data, $tahun, $bulan) {
        $bulanNama = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $bulanText = isset($bulanNama[(int)$bulan]) ? $bulanNama[(int)$bulan] : $bulan;
        $filename = 'Omset_Penjualan_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '_' . date('Y-m-d_H-i-s') . '.pdf';

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Omset Penjualan</title>
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
    <h1>📊 Laporan Omset Penjualan</h1>
    <div class="header-info">
        <p><strong>Periode:</strong> ' . $bulanText . ' ' . $tahun . '</p>
        <p><strong>Tanggal Laporan:</strong> ' . date('d F Y, H:i:s') . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Kode Sales</th>
                <th style="width: 12%;">Nama Sales</th>
                <th style="width: 8%;">Jml Outlet</th>
                <th style="width: 10%;">Penjualan</th>
                <th style="width: 10%;">Retur</th>
                <th style="width: 10%;">Penjualan Bersih</th>
                <th style="width: 10%;">Target Penjualan</th>
                <th style="width: 8%;">Prosen %</th>
                <th style="width: 10%;">Penerimaan Tunai</th>
                <th style="width: 8%;">CN</th>
                <th style="width: 8%;">Giro</th>
                <th style="width: 10%;">Penerimaan Bersih</th>
                <th style="width: 10%;">Target Penerimaan</th>
                <th style="width: 8%;">Prosen %</th>
            </tr>
        </thead>
        <tbody>';

        $no = 1;
        $totalJumlahFaktur = 0;
        $totalPenjualan = 0;
        $totalRetur = 0;
        $totalPenjualanBersih = 0;
        $totalTargetPenjualan = 0;
        $totalPenerimaanTunai = 0;
        $totalCN = 0;
        $totalGiro = 0;
        $totalPenerimaanBersih = 0;
        $totalTargetPenerimaan = 0;

        foreach ($data as $row) {
            $jumlahFaktur = (float)($row['jumlahfaktur'] ?? 0);
            $penjualan = (float)($row['penjualan'] ?? 0);
            $retur = (float)($row['returpenjualan'] ?? 0);
            $penjualanBersih = (float)($row['penjualanbersih'] ?? 0);
            $targetPenjualan = (float)($row['targetpenjualan'] ?? 0);
            $prosenPenjualan = (float)($row['prosenpenjualan'] ?? 0);
            $penerimaanTunai = (float)($row['penerimaantunai'] ?? 0);
            $cn = (float)($row['cnpenjualan'] ?? 0);
            $giro = (float)($row['pencairangiro'] ?? 0);
            $penerimaanBersih = (float)($row['penerimaanbersih'] ?? 0);
            $targetPenerimaan = (float)($row['targetpenerimaan'] ?? 0);
            $prosenPenerimaan = (float)($row['prosenpenerimaan'] ?? 0);

            $totalJumlahFaktur += $jumlahFaktur;
            $totalPenjualan += $penjualan;
            $totalRetur += $retur;
            $totalPenjualanBersih += $penjualanBersih;
            $totalTargetPenjualan += $targetPenjualan;
            $totalPenerimaanTunai += $penerimaanTunai;
            $totalCN += $cn;
            $totalGiro += $giro;
            $totalPenerimaanBersih += $penerimaanBersih;
            $totalTargetPenerimaan += $targetPenerimaan;

            $html .= '<tr>
                <td>' . $no++ . '</td>
                <td>' . htmlspecialchars($row['kodesales'] ?? '-') . '</td>
                <td class="text-left">' . htmlspecialchars($row['namasales'] ?? '-') . '</td>
                <td>' . number_format($jumlahFaktur, 0, ',', '.') . '</td>
                <td>' . number_format($penjualan, 0, ',', '.') . '</td>
                <td>' . number_format($retur, 0, ',', '.') . '</td>
                <td>' . number_format($penjualanBersih, 0, ',', '.') . '</td>
                <td>' . number_format($targetPenjualan, 0, ',', '.') . '</td>
                <td>' . number_format($prosenPenjualan, 2, ',', '.') . '</td>
                <td>' . number_format($penerimaanTunai, 0, ',', '.') . '</td>
                <td>' . number_format($cn, 0, ',', '.') . '</td>
                <td>' . number_format($giro, 0, ',', '.') . '</td>
                <td>' . number_format($penerimaanBersih, 0, ',', '.') . '</td>
                <td>' . number_format($targetPenerimaan, 0, ',', '.') . '</td>
                <td>' . number_format($prosenPenerimaan, 2, ',', '.') . '</td>
            </tr>';
        }

        // Total Row
        $totalProsenPenjualan = $totalTargetPenjualan > 0 ? ($totalPenjualanBersih / $totalTargetPenjualan) * 100 : 0;
        $totalProsenPenerimaan = $totalTargetPenerimaan > 0 ? ($totalPenerimaanBersih / $totalTargetPenerimaan) * 100 : 0;

        $html .= '<tr class="total-row">
            <td colspan="3" style="text-align: center;">TOTAL</td>
            <td>' . number_format($totalJumlahFaktur, 0, ',', '.') . '</td>
            <td>' . number_format($totalPenjualan, 0, ',', '.') . '</td>
            <td>' . number_format($totalRetur, 0, ',', '.') . '</td>
            <td>' . number_format($totalPenjualanBersih, 0, ',', '.') . '</td>
            <td>' . number_format($totalTargetPenjualan, 0, ',', '.') . '</td>
            <td>' . number_format($totalProsenPenjualan, 2, ',', '.') . '</td>
            <td>' . number_format($totalPenerimaanTunai, 0, ',', '.') . '</td>
            <td>' . number_format($totalCN, 0, ',', '.') . '</td>
            <td>' . number_format($totalGiro, 0, ',', '.') . '</td>
            <td>' . number_format($totalPenerimaanBersih, 0, ',', '.') . '</td>
            <td>' . number_format($totalTargetPenerimaan, 0, ',', '.') . '</td>
            <td>' . number_format($totalProsenPenerimaan, 2, ',', '.') . '</td>
        </tr>';

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

