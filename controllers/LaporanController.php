<?php
class LaporanController extends Controller {
    private $barangModel;
    private $pabrikModel;
    private $golonganModel;
    private $salesModel;
    private $customerModel;

    public function __construct() {
        parent::__construct();
        require_once __DIR__ . '/../core/LaporanPDF.php';
        $this->barangModel = new Masterbarang();
        $this->pabrikModel = new Tabelpabrik();
        $this->golonganModel = new Tabelgolongan();
        $this->salesModel = new Mastersales();
        $this->customerModel = new Mastercustomer();
    }

    public function daftarBarang() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodepabrik = trim($_GET['kodepabrik'] ?? '');
        $kodegolongan = trim($_GET['kodegolongan'] ?? '');
        $kondisiStok = $_GET['kondisi_stok'] ?? 'semua'; // 'semua', 'ada', 'kosong'
        $sortBy = $_GET['sort_by'] ?? 'namabarang';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            // For export, get all data
            $barangs = $this->getAllBarangsForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder);
            
            if ($export === 'excel') {
                $this->exportExcel($barangs);
            } elseif ($export === 'pdf') {
                $this->exportPDF($barangs);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $barangs = $this->getBarangsForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder, $page, $perPage);
        $total = $this->countBarangsForReport($search, $kodepabrik, $kodegolongan, $kondisiStok);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        // Get pabrik and golongan for dropdown
        $pabriks = $this->pabrikModel->getAllActive();
        $golongans = $this->golonganModel->getAllActive();

        $data = [
            'barangs' => $barangs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodepabrik' => $kodepabrik,
            'kodegolongan' => $kodegolongan,
            'kondisiStok' => $kondisiStok,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
        ];

        $this->view('laporan/daftar-barang', $data);
    }

    private function getBarangsForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['kodebarang', 'namabarang', 'golongan', 'pabrik'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'kodebarang' => 'mb.kodebarang',
            'namabarang' => 'mb.namabarang',
            'golongan' => 'tg.namagolongan',
            'pabrik' => 'tp.namapabrik'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.kodebarang,
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik AS pabrik,
                    tg.namagolongan AS golongan,
                    mb.kandungan
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    private function getAllBarangsForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['kodebarang', 'namabarang', 'golongan', 'pabrik'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'kodebarang' => 'mb.kodebarang',
            'namabarang' => 'mb.namabarang',
            'golongan' => 'tg.namagolongan',
            'pabrik' => 'tp.namapabrik'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.kodebarang,
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik AS pabrik,
                    tg.namagolongan AS golongan,
                    mb.kandungan
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}";

        return $this->db->fetchAll($sql, $params);
    }

    private function countBarangsForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total 
                FROM masterbarang mb 
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function exportExcel($barangs) {
        $filename = 'Laporan_Daftar_Barang_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['Kode Barang', 'Nama Barang', 'Satuan', 'Pabrik', 'Golongan', 'Kandungan'], ';');

        // Data
        foreach ($barangs as $barang) {
            fputcsv($output, [
                $barang['kodebarang'] ?? '',
                $barang['namabarang'] ?? '',
                $barang['satuan'] ?? '',
                $barang['pabrik'] ?? '',
                $barang['golongan'] ?? '',
                $barang['kandungan'] ?? ''
            ], ';');
        }

        fclose($output);
    }

    private function exportPDF($barangs) {
        // Generate PDF using simple HTML to PDF conversion
        $this->generateAndDownloadPDF('daftar-barang', $barangs);
    }

    private function generateAndDownloadPDF($reportType, $data) {
        $filename = 'Daftar_Barang_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Laporan Daftar Barang';
        $pdf->reportSubtitle = "Tanggal Laporan: " . date('d F Y') . "\nTotal Barang: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'Kode', 'Nama Barang', 'Satuan', 'Pabrik', 'Golongan', 'Kandungan'];
        $widths = [10, 25, 50, 15, 30, 30, 30];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, $d['kodebarang'] ?? '-', 1, 0, 'L');
            $pdf->Cell($widths[2], 6, substr($d['namabarang'] ?? '-', 0, 35), 1, 0, 'L');
            $pdf->Cell($widths[3], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[4], 6, substr($d['pabrik'] ?? '-', 0, 15), 1, 0, 'L');
            $pdf->Cell($widths[5], 6, substr($d['golongan'] ?? '-', 0, 15), 1, 0, 'L');
            $pdf->Cell($widths[6], 6, substr($d['kandungan'] ?? '-', 0, 18), 1, 0, 'L');
            $pdf->Ln();
        }

        $pdf->Output('D', $filename);
    }

    private function downloadAsHTML($html, $filename) {
        // Send as downloadable file
        // Detect if it's a PDF or HTML based on filename
        $isPDF = strpos($filename, '.pdf') !== false;
        
        if ($isPDF) {
            // For PDF files, use application/pdf MIME type
            header('Content-Type: application/pdf; charset=utf-8');
        } else {
            // For HTML files, use text/html MIME type
            header('Content-Type: text/html; charset=utf-8');
        }
        
        // Don't append extension - filename already has it
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        
        echo $html;
    }

    public function daftarStok() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodepabrik = trim($_GET['kodepabrik'] ?? '');
        $kodegolongan = trim($_GET['kodegolongan'] ?? '');
        $kondisiStok = $_GET['kondisi_stok'] ?? 'semua'; // 'semua', 'ada', 'kosong'
        $sortBy = $_GET['sort_by'] ?? 'namabarang';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            // For export, get all data
            $barangs = $this->getAllStoksForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder);
            
            if ($export === 'excel') {
                $this->exportExcelStok($barangs);
            } elseif ($export === 'pdf') {
                $this->exportPDFStok($barangs);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $barangs = $this->getStoksForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder, $page, $perPage);
        $total = $this->countStoksForReport($search, $kodepabrik, $kodegolongan, $kondisiStok);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        // Get pabrik and golongan for dropdown
        $pabriks = $this->pabrikModel->getAllActive();
        $golongans = $this->golonganModel->getAllActive();

        $data = [
            'barangs' => $barangs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodepabrik' => $kodepabrik,
            'kodegolongan' => $kodegolongan,
            'kondisiStok' => $kondisiStok,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
        ];

        $this->view('laporan/daftar-stok', $data);
    }

    private function getStoksForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'hargajual', 'discountjual', 'kondisi', 'stok'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual',
            'kondisi' => 'mb.kondisi',
            'stok' => 'mb.stokakhir'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    mb.hargajual,
                    mb.discountjual,
                    mb.kondisi,
                    mb.ed,
                    tp.namapabrik AS pabrik,
                    mb.stokakhir AS stok
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    private function getAllStoksForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'hargajual', 'discountjual', 'kondisi', 'stok'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual',
            'kondisi' => 'mb.kondisi',
            'stok' => 'mb.stokakhir'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    mb.hargajual,
                    mb.discountjual,
                    mb.kondisi,
                    mb.ed,
                    tp.namapabrik AS pabrik,
                    mb.stokakhir AS stok
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}";

        return $this->db->fetchAll($sql, $params);
    }

    private function countStoksForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total 
                FROM masterbarang mb 
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function exportExcelStok($barangs) {
        $filename = 'Laporan_Daftar_Stok_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['Nama Barang', 'Satuan', 'Harga Jual', 'Discount', 'Kondisi', 'Stok'], ';');

        // Data
        foreach ($barangs as $barang) {
            fputcsv($output, [
                $barang['namabarang'] ?? '',
                $barang['satuan'] ?? '',
                $barang['hargajual'] ?? '0',
                $barang['discountjual'] ?? '0',
                $barang['kondisi'] ?? '-',
                $barang['stok'] ?? '0'
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFStok($barangs) {
        $this->generateAndDownloadPDFStok($barangs);
    }

    private function generateAndDownloadPDFStok($data) {
        $filename = 'Daftar_Stok_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Laporan Daftar Stok';
        $pdf->reportSubtitle = "Tanggal Laporan: " . date('d F Y') . "\nTotal Barang: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'Nama Barang', 'Satuan', 'Harga Jual', 'Disc', 'Stok'];
        $widths = [10, 95, 20, 25, 15, 15];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($d['namabarang'] ?? '-', 0, 55), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[3], 6, number_format((float)($d['hargajual'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[4], 6, number_format((float)($d['discountjual'] ?? 0), 2, ',', '.') . '%', 1, 0, 'R');
            $pdf->Cell($widths[5], 6, number_format((float)($d['stok'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('D', $filename);
    }

    public function laporanPersediaan() {
        Auth::requireRole(['admin', 'manajemen', 'operator']);

        $search = trim($_GET['search'] ?? '');
        $kodepabrik = trim($_GET['kodepabrik'] ?? '');
        $kodegolongan = trim($_GET['kodegolongan'] ?? '');
        $kondisiStok = $_GET['kondisi_stok'] ?? 'semua'; // 'semua', 'ada', 'kosong'
        $sortBy = $_GET['sort_by'] ?? 'namabarang';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            // For export, get all data
            $barangs = $this->getAllPersediaanForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder);
            
            if ($export === 'excel') {
                $this->exportExcelPersediaan($barangs);
            } elseif ($export === 'pdf') {
                $this->exportPDFPersediaan($barangs);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $barangs = $this->getPersediaanForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder, $page, $perPage);
        $total = $this->countPersediaanForReport($search, $kodepabrik, $kodegolongan, $kondisiStok);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        // Get pabrik and golongan for dropdown
        $pabriks = $this->pabrikModel->getAllActive();
        $golongans = $this->golonganModel->getAllActive();

        $data = [
            'barangs' => $barangs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodepabrik' => $kodepabrik,
            'kodegolongan' => $kodegolongan,
            'kondisiStok' => $kondisiStok,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
        ];

        $this->view('laporan/persediaan', $data);
    }

    private function getPersediaanForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'hargajual', 'discountjual', 'kondisi', 'stok', 'hpp', 'nilai_persediaan'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual',
            'kondisi' => 'mb.kondisi',
            'stok' => 'mb.stokakhir',
            'hpp' => 'mb.hpp',
            'nilai_persediaan' => '(mb.stokakhir * mb.hpp)'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    mb.hargajual,
                    mb.discountjual,
                    mb.kondisi,
                    mb.stokakhir AS stok,
                    mb.hpp,
                    (mb.stokakhir * mb.hpp) as nilai_persediaan
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    private function getAllPersediaanForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'hargajual', 'discountjual', 'kondisi', 'stok', 'hpp', 'nilai_persediaan'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual',
            'kondisi' => 'mb.kondisi',
            'stok' => 'mb.stokakhir',
            'hpp' => 'mb.hpp',
            'nilai_persediaan' => '(mb.stokakhir * mb.hpp)'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    mb.hargajual,
                    mb.discountjual,
                    mb.kondisi,
                    mb.stokakhir AS stok,
                    mb.hpp,
                    (mb.stokakhir * mb.hpp) as nilai_persediaan
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}";

        return $this->db->fetchAll($sql, $params);
    }

    private function countPersediaanForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total 
                FROM masterbarang mb 
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function exportExcelPersediaan($barangs) {
        $filename = 'Laporan_Persediaan_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['Nama Barang', 'Satuan', 'HPP', 'Stok', 'Nilai Persediaan'], ';');

        // Data
        foreach ($barangs as $barang) {
            fputcsv($output, [
                $barang['namabarang'] ?? '',
                $barang['satuan'] ?? '',
                $barang['hpp'] ?? '0',
                $barang['stok'] ?? '0',
                $barang['nilai_persediaan'] ?? '0'
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFPersediaan($barangs) {
        $this->generateAndDownloadPDFPersediaan($barangs);
    }

    private function generateAndDownloadPDFPersediaan($data) {
        $filename = 'Daftar_Persediaan_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Daftar Persediaan';
        $pdf->reportSubtitle = "Tanggal Laporan: " . date('d F Y') . "\nTotal Barang: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'Nama Barang', 'Satuan', 'HPP', 'Stok', 'Nilai Persediaan'];
        $widths = [10, 80, 20, 25, 20, 35];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;
        
        $totalNilai = 0;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($d['namabarang'] ?? '-', 0, 45), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[3], 6, number_format((float)($d['hpp'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[4], 6, number_format((float)($d['stok'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[5], 6, number_format((float)($d['nilai_persediaan'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Ln();
            
            $totalNilai += (float)($d['nilai_persediaan'] ?? 0);
        }
        
        // Total row
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell($widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4], 6, 'Total Nilai Persediaan', 1, 0, 'R');
        $pdf->Cell($widths[5], 6, number_format($totalNilai, 0, ',', '.'), 1, 0, 'R');
        $pdf->Ln();

        $pdf->Output('D', $filename);
    }

    public function daftarHarga() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodepabrik = trim($_GET['kodepabrik'] ?? '');
        $kodegolongan = trim($_GET['kodegolongan'] ?? '');
        $kondisiStok = $_GET['kondisi_stok'] ?? 'semua'; // 'semua', 'ada', 'kosong'
        $sortBy = $_GET['sort_by'] ?? 'namabarang';
        $sortOrder = $_GET['sort_order'] ?? 'ASC';
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            // For export, get all data
            $barangs = $this->getAllHargasForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder);
            
            if ($export === 'excel') {
                $this->exportExcelHarga($barangs);
            } elseif ($export === 'pdf') {
                $this->exportPDFHarga($barangs);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $barangs = $this->getHargasForReport($search, $kodepabrik, $kodegolongan, $kondisiStok, $sortBy, $sortOrder, $page, $perPage);
        $total = $this->countHargasForReport($search, $kodepabrik, $kodegolongan, $kondisiStok);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        // Get pabrik and golongan for dropdown
        $pabriks = $this->pabrikModel->getAllActive();
        $golongans = $this->golonganModel->getAllActive();

        $data = [
            'barangs' => $barangs,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodepabrik' => $kodepabrik,
            'kodegolongan' => $kodegolongan,
            'kondisiStok' => $kondisiStok,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'pabriks' => $pabriks,
            'golongans' => $golongans,
        ];

        $this->view('laporan/daftar-harga', $data);
    }

    private function getHargasForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC', $page = 1, $perPage = 10) {
        $offset = ($page - 1) * $perPage;
        
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'pabrik', 'hargajual', 'discountjual'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'pabrik' => 'tp.namapabrik',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik AS pabrik,
                    mb.kondisi,
                    mb.ed,
                    mb.stokakhir AS stok,
                    mb.hargajual,
                    mb.discountjual
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    private function getAllHargasForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua', $sortBy = 'namabarang', $sortOrder = 'ASC') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        // Validate sort column
        $validSortColumns = ['namabarang', 'satuan', 'pabrik', 'hargajual', 'discountjual'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'namabarang';
        $sortOrder = strtoupper($sortOrder) === 'DESC' ? 'DESC' : 'ASC';

        // Map sort column to actual database column
        $sortColumnMap = [
            'namabarang' => 'mb.namabarang',
            'satuan' => 'mb.satuan',
            'pabrik' => 'tp.namapabrik',
            'hargajual' => 'mb.hargajual',
            'discountjual' => 'mb.discountjual'
        ];
        $orderByColumn = $sortColumnMap[$sortBy] ?? 'mb.namabarang';

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik AS pabrik,
                    mb.kondisi,
                    mb.ed,
                    mb.stokakhir AS stok,
                    mb.hargajual,
                    mb.discountjual
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                LEFT JOIN tabelgolongan tg ON mb.kodegolongan = tg.kodegolongan
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}";

        return $this->db->fetchAll($sql, $params);
    }

    private function countHargasForReport($search = '', $kodepabrik = '', $kodegolongan = '', $kondisiStok = 'semua') {
        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mb.namabarang LIKE ? OR mb.kandungan LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodepabrik)) {
            $where[] = "mb.kodepabrik = ?";
            $params[] = $kodepabrik;
        }

        if (!empty($kodegolongan)) {
            $where[] = "mb.kodegolongan = ?";
            $params[] = $kodegolongan;
        }

        if ($kondisiStok === 'ada') {
            $where[] = "mb.stokakhir > 0";
        } elseif ($kondisiStok === 'kosong') {
            $where[] = "(mb.stokakhir = 0 OR mb.stokakhir IS NULL)";
        }

        $whereClause = implode(' AND ', $where);
        $sql = "SELECT COUNT(*) as total 
                FROM masterbarang mb 
                WHERE {$whereClause}";

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function exportExcelHarga($barangs) {
        $filename = 'Laporan_Daftar_Harga_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['No', 'Nama Barang', 'Stok', 'Harga Jual', 'Satuan'], ';');

        // Data
        $no = 1;
        foreach ($barangs as $barang) {
            fputcsv($output, [
                $no++,
                $barang['namabarang'] ?? '',
                $barang['stok'] ?? '0',
                $barang['hargajual'] ?? '0',
                $barang['satuan'] ?? ''
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFHarga($barangs) {
        $this->generateAndDownloadPDFHarga($barangs);
    }

    private function generateAndDownloadPDFHarga($data) {
        $filename = 'Daftar_Harga_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Laporan Daftar Harga';
        $pdf->reportSubtitle = "Tanggal Laporan: " . date('d F Y') . "\nTotal Barang: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'Nama Barang', 'Stok', 'Harga', 'Satuan'];
        $widths = [10, 95, 20, 30, 20];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($d['namabarang'] ?? '-', 0, 55), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, number_format((float)($d['stok'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[3], 6, number_format((float)($d['hargajual'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[4], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Ln();
        }

        $pdf->Output('D', $filename);
    }

    public function daftarTagihan() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodecustomer = trim($_GET['kodecustomer'] ?? '');
        $statusJatuhTempo = $_GET['status_jatuh_tempo'] ?? 'semua'; // 'semua', 'sudah', 'belum'
        $sortBy = $_GET['sort_by'] ?? 'umur';
        $sortOrder = $_GET['sort_order'] ?? 'DESC';
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'

        // Check if user is sales
        $kodesales = null;
        if (Auth::isSales()) {
            $user = Auth::user();
            $kodesales = $user['kodesales'] ?? null;
        }

        // Get all data for export, or paginated for display
        if (!empty($export)) {
            // For export, get all data
            $tagihans = $this->getAllTagihansForReport($search, $kodecustomer, $statusJatuhTempo, $sortBy, $sortOrder, $kodesales);
            
            if ($export === 'excel') {
                $this->exportExcelTagihan($tagihans);
            } elseif ($export === 'pdf') {
                $this->exportPDFTagihan($tagihans);
            }
            exit;
        }

        // For display, use pagination
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 10;

        $tagihans = $this->getTagihansForReport($search, $kodecustomer, $statusJatuhTempo, $sortBy, $sortOrder, $page, $perPage, $kodesales);
        $total = $this->countTagihansForReport($search, $kodecustomer, $statusJatuhTempo, $kodesales);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        // Calculate totals for current page
        $totals = [
            'nilaipenjualan' => 0,
            'saldopenjualan' => 0
        ];
        foreach ($tagihans as $tagihan) {
            $totals['nilaipenjualan'] += (float)($tagihan['nilaipenjualan'] ?? 0);
            $totals['saldopenjualan'] += (float)($tagihan['saldopenjualan'] ?? 0);
        }

        // Calculate grand total from all data (not paginated)
        $grandTotals = $this->getGrandTotalsForReport($search, $kodecustomer, $statusJatuhTempo, $kodesales);

        // Get customers for dropdown
        $customerModel = new Mastercustomer();
        // $customers = $customerModel->getAllForSelection();
        $customers = $this->getCustomersForTagihanDropdown($statusJatuhTempo, $kodesales);

        $data = [
            'tagihans' => $tagihans,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodecustomer' => $kodecustomer,
            'statusJatuhTempo' => $statusJatuhTempo,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'customers' => $customers,
            'totals' => $totals,
            'grandTotals' => $grandTotals,
        ];

        $this->view('laporan/daftar-tagihan', $data);
    }

    private function getTagihansForReport($search = '', $kodecustomer = '', $statusJatuhTempo = 'semua', $sortBy = 'tanggalpenjualan', $sortOrder = 'DESC', $page = 1, $perPage = 10, $kodesales = null) {
        $offset = ($page - 1) * $perPage;
        $tanggalSistem = date('Y-m-d');
        
        $where = ["hp.saldopenjualan > 0"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR mc.namabadanusaha LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodecustomer)) {
            $where[] = "hp.kodecustomer = ?";
            $params[] = $kodecustomer;
        }

        if ($statusJatuhTempo === 'sudah') {
            $where[] = "hp.tanggaljatuhtempo < ?";
            $params[] = $tanggalSistem;
        } elseif ($statusJatuhTempo === 'belum') {
            $where[] = "(hp.tanggaljatuhtempo >= ? OR hp.tanggaljatuhtempo IS NULL)";
            $params[] = $tanggalSistem;
        }

        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }

        // Validate sort column
        $validSortColumns = ['nopenjualan', 'tanggalpenjualan', 'tanggaljatuhtempo', 'namacustomer', 'nilaipenjualan', 'saldopenjualan', 'umur'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'umur';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = implode(' AND ', $where);
        
        // Determine order by column
        if ($sortBy === 'namacustomer') {
            $orderByColumn = 'mc.namacustomer';
        } elseif ($sortBy === 'umur') {
            $orderByColumn = 'DATEDIFF(CURDATE(), hp.tanggalpenjualan)';
        } else {
            $orderByColumn = "hp.{$sortBy}";
        }

        $sql = "SELECT 
                    hp.nopenjualan,
                    hp.tanggalpenjualan,
                    hp.tanggaljatuhtempo,
                    hp.nilaipenjualan,
                    hp.saldopenjualan,
                    mc.namacustomer,
                    mc.namabadanusaha,
                    mc.alamatcustomer,
                    DATEDIFF(CURDATE(), hp.tanggalpenjualan) AS umur
                FROM headerpenjualan hp
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    private function countTagihansForReport($search = '', $kodecustomer = '', $statusJatuhTempo = 'semua', $kodesales = null) {
        $tanggalSistem = date('Y-m-d');
        
        $where = ["hp.saldopenjualan > 0"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR mc.namabadanusaha LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodecustomer)) {
            $where[] = "hp.kodecustomer = ?";
            $params[] = $kodecustomer;
        }

        if ($statusJatuhTempo === 'sudah') {
            $where[] = "hp.tanggaljatuhtempo < ?";
            $params[] = $tanggalSistem;
        } elseif ($statusJatuhTempo === 'belum') {
            $where[] = "(hp.tanggaljatuhtempo >= ? OR hp.tanggaljatuhtempo IS NULL)";
            $params[] = $tanggalSistem;
        }

        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT COUNT(*) as total
                FROM headerpenjualan hp
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return $result ? (int)$result['total'] : 0;
    }

    private function getGrandTotalsForReport($search = '', $kodecustomer = '', $statusJatuhTempo = 'semua', $kodesales = null) {
        $tanggalSistem = date('Y-m-d');
        
        $where = ["hp.saldopenjualan > 0"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR mc.namabadanusaha LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodecustomer)) {
            $where[] = "hp.kodecustomer = ?";
            $params[] = $kodecustomer;
        }

        if ($statusJatuhTempo === 'sudah') {
            $where[] = "hp.tanggaljatuhtempo < ?";
            $params[] = $tanggalSistem;
        } elseif ($statusJatuhTempo === 'belum') {
            $where[] = "(hp.tanggaljatuhtempo >= ? OR hp.tanggaljatuhtempo IS NULL)";
            $params[] = $tanggalSistem;
        }

        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    SUM(hp.nilaipenjualan) as total_nilaipenjualan,
                    SUM(hp.saldopenjualan) as total_saldopenjualan
                FROM headerpenjualan hp
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}";
        
        $result = $this->db->fetchOne($sql, $params);
        return [
            'nilaipenjualan' => (float)($result['total_nilaipenjualan'] ?? 0),
            'saldopenjualan' => (float)($result['total_saldopenjualan'] ?? 0)
        ];
    }

    private function getAllTagihansForReport($search = '', $kodecustomer = '', $statusJatuhTempo = 'semua', $sortBy = 'umur', $sortOrder = 'DESC', $kodesales = null) {
        $tanggalSistem = date('Y-m-d');
        
        $where = ["hp.saldopenjualan > 0"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR mc.namabadanusaha LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if (!empty($kodecustomer)) {
            $where[] = "hp.kodecustomer = ?";
            $params[] = $kodecustomer;
        }

        if ($statusJatuhTempo === 'sudah') {
            $where[] = "hp.tanggaljatuhtempo < ?";
            $params[] = $tanggalSistem;
        } elseif ($statusJatuhTempo === 'belum') {
            $where[] = "(hp.tanggaljatuhtempo >= ? OR hp.tanggaljatuhtempo IS NULL)";
            $params[] = $tanggalSistem;
        }

        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }

        // Validate sort column
        $validSortColumns = ['nopenjualan', 'tanggalpenjualan', 'tanggaljatuhtempo', 'namacustomer', 'nilaipenjualan', 'saldopenjualan', 'umur'];
        $sortBy = in_array($sortBy, $validSortColumns) ? $sortBy : 'umur';
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        $whereClause = implode(' AND ', $where);
        
        // Determine order by column
        if ($sortBy === 'namacustomer') {
            $orderByColumn = 'mc.namacustomer';
        } elseif ($sortBy === 'umur') {
            $orderByColumn = 'DATEDIFF(CURDATE(), hp.tanggalpenjualan)';
        } else {
            $orderByColumn = "hp.{$sortBy}";
        }

        $sql = "SELECT 
                    hp.nopenjualan,
                    hp.tanggalpenjualan,
                    hp.tanggaljatuhtempo,
                    hp.nilaipenjualan,
                    hp.saldopenjualan,
                    mc.namacustomer,
                    mc.namabadanusaha,
                    mc.alamatcustomer,
                    DATEDIFF(CURDATE(), hp.tanggalpenjualan) AS umur
                FROM headerpenjualan hp
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}
                ORDER BY {$orderByColumn} {$sortOrder}";
        
        return $this->db->fetchAll($sql, $params);
    }

    private function exportExcelTagihan($tagihans) {
        $filename = 'Laporan_Daftar_Tagihan_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['No.Faktur', 'Tanggal Penjualan', 'Umur', 'Jatuh Tempo', 'Customer', 'Alamat Customer', 'Nilai Penjualan', 'Saldo Tagihan'], ';');

        // Data
        $tanggalSistem = new DateTime();
        foreach ($tagihans as $tagihan) {
            // Hitung umur
            $umur = '-';
            if (!empty($tagihan['tanggalpenjualan'])) {
                try {
                    $tanggalPenjualan = new DateTime($tagihan['tanggalpenjualan']);
                    $diff = $tanggalSistem->diff($tanggalPenjualan);
                    $umur = $diff->days;
                } catch (Exception $e) {
                    $umur = '-';
                }
            }

            // Format customer dengan namabadanusaha
            $customerDisplay = $tagihan['namacustomer'] ?? '';
            if ($customerDisplay && !empty($tagihan['namabadanusaha'])) {
                $customerDisplay .= ', ' . $tagihan['namabadanusaha'];
            }

            fputcsv($output, [
                $tagihan['nopenjualan'] ?? '',
                $tagihan['tanggalpenjualan'] ? date('d/m/Y', strtotime($tagihan['tanggalpenjualan'])) : '',
                $umur !== '-' ? $umur . ' hari' : '-',
                $tagihan['tanggaljatuhtempo'] ? date('d/m/Y', strtotime($tagihan['tanggaljatuhtempo'])) : '',
                $customerDisplay ?: '',
                $tagihan['alamatcustomer'] ?? '',
                number_format((float)($tagihan['nilaipenjualan'] ?? 0), 0, ',', '.'),
                number_format((float)($tagihan['saldopenjualan'] ?? 0), 0, ',', '.')
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFTagihan($tagihans) {
        $this->generateAndDownloadPDFTagihan($tagihans);
    }

    private function generateAndDownloadPDFTagihan($data) {
        $filename = 'Daftar_Tagihan_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Laporan Daftar Tagihan';
        $pdf->reportSubtitle = "Tanggal Laporan: " . date('d F Y') . "\nTotal Transaksi: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'No.Faktur', 'Tanggal', 'Umur', 'Jatuh Tempo', 'Customer', 'Nilai', 'Saldo'];
        $widths = [8, 25, 20, 10, 20, 57, 25, 25];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 7);
        $no = 1;
        $tanggalSistem = new DateTime();
        $totalNilai = 0;
        $totalSaldo = 0;

        foreach ($data as $d) {
            $umur = '-';
            if (!empty($d['tanggalpenjualan'])) {
                try {
                    $tgl = new DateTime($d['tanggalpenjualan']);
                    $diff = $tanggalSistem->diff($tgl);
                    $umur = $diff->days;
                } catch (Exception $e) {}
            }

            $customer = $d['namacustomer'] ?? '';
            if ($customer && !empty($d['namabadanusaha'])) {
                $customer .= ', ' . $d['namabadanusaha'];
            }

            $nilai = (float)($d['nilaipenjualan'] ?? 0);
            $saldo = (float)($d['saldopenjualan'] ?? 0);
            $totalNilai += $nilai;
            $totalSaldo += $saldo;

            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, $d['nopenjualan'] ?? '-', 1, 0, 'L');
            $pdf->Cell($widths[2], 6, $d['tanggalpenjualan'] ? date('d/m/Y', strtotime($d['tanggalpenjualan'])) : '-', 1, 0, 'C');
            $pdf->Cell($widths[3], 6, ($umur !== '-' ? $umur . ' h' : '-'), 1, 0, 'C');
            $pdf->Cell($widths[4], 6, $d['tanggaljatuhtempo'] ? date('d/m/Y', strtotime($d['tanggaljatuhtempo'])) : '-', 1, 0, 'C');
            $pdf->Cell($widths[5], 6, substr($customer, 0, 40), 1, 0, 'L');
            $pdf->Cell($widths[6], 6, number_format($nilai, 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[7], 6, number_format($saldo, 0, ',', '.'), 1, 0, 'R');
            $pdf->Ln();
        }

        // Total row
        $pdf->SetFont('Helvetica', 'B', 7);
        $pdf->Cell($widths[0] + $widths[1] + $widths[2] + $widths[3] + $widths[4] + $widths[5], 6, 'TOTAL', 1, 0, 'R');
        $pdf->Cell($widths[6], 6, number_format($totalNilai, 0, ',', '.'), 1, 0, 'R');
        $pdf->Cell($widths[7], 6, number_format($totalSaldo, 0, ',', '.'), 1, 0, 'R');

        $pdf->Output('D', $filename);
    }
    public function distribusiPenjualan() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodesales = trim($_GET['kodesales'] ?? '');
        $kodecustomer = trim($_GET['kodecustomer'] ?? '');
        $periode = $_GET['periode'] ?? 'today';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        // Apply sales role restriction
        if (Auth::isSales()) {
            $user = Auth::user();
            $kodesales = $user['kodesales'] ?? '';
        }

        $filters = [
            'search' => $search,
            'kodesales' => $kodesales,
            'kodecustomer' => $kodecustomer,
            'periode' => $periode,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        // Handle export
        $export = $_GET['export'] ?? '';
        if (!empty($export)) {
            $data = $this->getDistribusiPenjualanData($filters);
            
            if ($export === 'excel') {
                $this->exportExcelDistribusiPenjualan($data, $filters);
            } elseif ($export === 'pdf') {
                $this->exportPDFDistribusiPenjualan($data, $filters);
            }
            exit;
        }

        $data = $this->getDistribusiPenjualanData($filters);
        $total = count($data);

        $viewData = [
            'reportData' => $data,
            'total' => $total,
            'search' => $search,
            'kodesales' => $kodesales,
            'kodecustomer' => $kodecustomer,
            'periode' => $periode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'salesList' => $this->salesModel->getAllActive(),
            'customerList' => $this->getCustomersForDropdown($filters)
        ];

        $this->view('laporan/distribusi-penjualan', $viewData);
    }

    private function getCustomersForDropdown($filters) {
        $params = [];
        $where = ["1=1"];

        // Filter by Sales if provided
        if (!empty($filters['kodesales'])) {
            $where[] = "hp.kodesales = ?";
            $params[] = $filters['kodesales'];
        }

        // Filter by Date
        if ($filters['periode'] === 'today') {
            $where[] = "DATE(hp.tanggalpenjualan) = CURDATE()";
        } elseif ($filters['periode'] === 'this_month') {
            $where[] = "MONTH(hp.tanggalpenjualan) = MONTH(CURDATE()) AND YEAR(hp.tanggalpenjualan) = YEAR(CURDATE())";
        } elseif ($filters['periode'] === 'this_year') {
            $where[] = "YEAR(hp.tanggalpenjualan) = YEAR(CURDATE())";
        } elseif ($filters['periode'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = "DATE(hp.tanggalpenjualan) BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT DISTINCT mc.kodecustomer, mc.namacustomer, mc.namabadanusaha
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}
                ORDER BY mc.namacustomer ASC";

        return $this->db->fetchAll($sql, $params);
    }

    private function getCustomersForTagihanDropdown($statusJatuhTempo, $kodesales) {
        $params = [];
        $where = ["hp.saldopenjualan > 0"];

        // Filter by Sales if provided
        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }

        // Filter by Jatuh Tempo Status
        $tanggalSistem = date('Y-m-d');
        if ($statusJatuhTempo === 'sudah') {
            $where[] = "hp.tanggaljatuhtempo < ?";
            $params[] = $tanggalSistem;
        } elseif ($statusJatuhTempo === 'belum') {
            $where[] = "hp.tanggaljatuhtempo >= ?";
            $params[] = $tanggalSistem;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT DISTINCT mc.kodecustomer, mc.namacustomer, mc.namabadanusaha, mc.alamatcustomer, mc.statuspkp
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                WHERE {$whereClause}
                ORDER BY mc.namacustomer ASC";

        return $this->db->fetchAll($sql, $params);
    }

    private function getDistribusiPenjualanData($filters) {
        $params = [];
        $where = ["1=1"];

        if (!empty($filters['search'])) {
            $where[] = "(mc.namacustomer LIKE ? OR mb.namabarang LIKE ?)";
            $params[] = "%" . $filters['search'] . "%";
            $params[] = "%" . $filters['search'] . "%";
        }

        if (!empty($filters['kodesales'])) {
            $where[] = "hp.kodesales = ?";
            $params[] = $filters['kodesales'];
        }

        if (!empty($filters['kodecustomer'])) {
            $where[] = "hp.kodecustomer = ?";
            $params[] = $filters['kodecustomer'];
        }

        // Date Filter
        if ($filters['periode'] === 'today') {
            $where[] = "DATE(hp.tanggalpenjualan) = CURDATE()";
        } elseif ($filters['periode'] === 'this_month') {
            $where[] = "MONTH(hp.tanggalpenjualan) = MONTH(CURDATE()) AND YEAR(hp.tanggalpenjualan) = YEAR(CURDATE())";
        } elseif ($filters['periode'] === 'this_year') {
            $where[] = "YEAR(hp.tanggalpenjualan) = YEAR(CURDATE())";
        } elseif ($filters['periode'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where[] = "DATE(hp.tanggalpenjualan) BETWEEN ? AND ?";
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        $whereClause = implode(' AND ', $where);

        $sql = "SELECT 
                    ms.namasales,
                    ms.kodesales,
                    mc.namacustomer,
                    mc.kodecustomer,
                    mc.namabadanusaha,
                    mc.alamatcustomer,
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik,
                    SUM(dp.jumlah) as total_jumlah,
                    SUM(dp.jumlahharga) as total_nilai,
                    (SUM(dp.jumlahharga) / NULLIF(SUM(dp.jumlah), 0)) as harga_rata_rata
                FROM detailpenjualan dp
                JOIN headerpenjualan hp ON dp.nopenjualan = hp.nopenjualan
                JOIN masterbarang mb ON dp.kodebarang = mb.kodebarang
                LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
                LEFT JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                WHERE {$whereClause}
                GROUP BY hp.kodesales, hp.kodecustomer, dp.kodebarang
                ORDER BY ms.namasales ASC, mc.namacustomer ASC, mb.namabarang ASC";

        return $this->db->fetchAll($sql, $params);
    }


    public function barangTidakTerjual() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $periode = $_GET['periode'] ?? 'this_month';
        $startDate = $_GET['start_date'] ?? '';
        $endDate = $_GET['end_date'] ?? '';
        $sortBy = $_GET['sort_by'] ?? 'namabarang';
        $sortOrder = strtoupper($_GET['sort_order'] ?? 'ASC');
        $export = $_GET['export'] ?? ''; // 'excel' or 'pdf'
        
        // Validate sort order
        if (!in_array($sortOrder, ['ASC', 'DESC'])) {
            $sortOrder = 'ASC';
        }
        
        // Validate sort column
        $allowedSortColumns = ['namabarang', 'namapabrik'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'namabarang';
        }

        $filters = [
            'search' => $search,
            'periode' => $periode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder
        ];

        // Handle export
        if (!empty($export)) {
            // Get all data for export (no pagination)
            $allData = $this->getAllBarangTidakTerjualData($filters);
            
            if ($export === 'excel') {
                $this->exportExcelBarangTidakTerjual($allData, $filters);
            } elseif ($export === 'pdf') {
                $this->exportPDFBarangTidakTerjual($allData, $filters);
            }
            exit;
        }

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;

        $data = $this->getBarangTidakTerjualData($filters, $page, $perPage);
        $total = $this->countBarangTidakTerjual($filters);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;

        $viewData = [
            'reportData' => $data,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'periode' => $periode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'perPageOptions' => [10, 25, 50, 100, 200, 500, 1000]
        ];

        $this->view('laporan/barang-tidak-terjual', $viewData);
    }

    private function getBarangTidakTerjualData($filters, $page, $perPage) {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $subqueryParams = [];
        $dateWhere = "1=1";

        // Build date filter for the subquery
        if ($filters['periode'] === 'today') {
            $dateWhere = "hp.tanggalpenjualan >= CURDATE() AND hp.tanggalpenjualan < CURDATE() + INTERVAL 1 DAY";
        } elseif ($filters['periode'] === 'this_month') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH";
        } elseif ($filters['periode'] === 'this_year') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR";
        } elseif ($filters['periode'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateWhere = "hp.tanggalpenjualan BETWEEN ? AND ?";
            $subqueryParams[] = $filters['start_date'];
            $subqueryParams[] = $filters['end_date'];
        }

        // Build the complete SQL with subquery parameters inline
        $subquerySql = "SELECT DISTINCT dp.kodebarang
                        FROM detailpenjualan dp
                        JOIN headerpenjualan hp ON dp.nopenjualan = hp.nopenjualan
                        WHERE {$dateWhere}";

        // Main query to get items NOT sold in the period
        $sql = "SELECT 
                    mb.kodebarang,
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik,
                    mb.stokakhir
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                WHERE mb.stokakhir > 0
                AND mb.kodebarang NOT IN ({$subquerySql})";

        // Add subquery params first
        $params = array_merge($params, $subqueryParams);

        // Add search filter
        if (!empty($filters['search'])) {
            $sql .= " AND mb.namabarang LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }

        // Add sorting
        $sortBy = $filters['sort_by'] ?? 'namabarang';
        $sortOrder = $filters['sort_order'] ?? 'ASC';
        
        // Map sort columns to actual table columns
        $sortColumn = $sortBy === 'namapabrik' ? 'tp.namapabrik' : 'mb.namabarang';
        
        $sql .= " ORDER BY {$sortColumn} {$sortOrder} LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;

        return $this->db->fetchAll($sql, $params);
    }

    private function countBarangTidakTerjual($filters) {
        $params = [];
        $subqueryParams = [];
        $dateWhere = "1=1";

        if ($filters['periode'] === 'today') {
            $dateWhere = "hp.tanggalpenjualan >= CURDATE() AND hp.tanggalpenjualan < CURDATE() + INTERVAL 1 DAY";
        } elseif ($filters['periode'] === 'this_month') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH";
        } elseif ($filters['periode'] === 'this_year') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR";
        } elseif ($filters['periode'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateWhere = "hp.tanggalpenjualan BETWEEN ? AND ?";
            $subqueryParams[] = $filters['start_date'];
            $subqueryParams[] = $filters['end_date'];
        }

        $subquerySql = "SELECT DISTINCT dp.kodebarang
                        FROM detailpenjualan dp
                        JOIN headerpenjualan hp ON dp.nopenjualan = hp.nopenjualan
                        WHERE {$dateWhere}";

        $sql = "SELECT COUNT(*) as total
                FROM masterbarang mb
                WHERE mb.stokakhir > 0
                AND mb.kodebarang NOT IN ({$subquerySql})";

        // Add subquery params first
        $params = array_merge($params, $subqueryParams);

        if (!empty($filters['search'])) {
            $sql .= " AND mb.namabarang LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function getAllBarangTidakTerjualData($filters) {
        $params = [];
        $subqueryParams = [];
        $dateWhere = "1=1";

        // Build date filter for the subquery
        if ($filters['periode'] === 'today') {
            $dateWhere = "hp.tanggalpenjualan >= CURDATE() AND hp.tanggalpenjualan < CURDATE() + INTERVAL 1 DAY";
        } elseif ($filters['periode'] === 'this_month') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-%m-01') + INTERVAL 1 MONTH";
        } elseif ($filters['periode'] === 'this_year') {
            $dateWhere = "hp.tanggalpenjualan >= DATE_FORMAT(CURDATE(), '%Y-01-01') AND hp.tanggalpenjualan < DATE_FORMAT(CURDATE(), '%Y-01-01') + INTERVAL 1 YEAR";
        } elseif ($filters['periode'] === 'custom' && !empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateWhere = "hp.tanggalpenjualan BETWEEN ? AND ?";
            $subqueryParams[] = $filters['start_date'];
            $subqueryParams[] = $filters['end_date'];
        }

        // Build the complete SQL with subquery parameters inline
        $subquerySql = "SELECT DISTINCT dp.kodebarang
                        FROM detailpenjualan dp
                        JOIN headerpenjualan hp ON dp.nopenjualan = hp.nopenjualan
                        WHERE {$dateWhere}";

        // Main query to get items NOT sold in the period
        $sql = "SELECT 
                    mb.kodebarang,
                    mb.namabarang,
                    mb.satuan,
                    tp.namapabrik,
                    mb.stokakhir
                FROM masterbarang mb
                LEFT JOIN tabelpabrik tp ON mb.kodepabrik = tp.kodepabrik
                WHERE mb.stokakhir > 0
                AND mb.kodebarang NOT IN ({$subquerySql})";

        // Add subquery params first
        $params = array_merge($params, $subqueryParams);

        // Add search filter
        if (!empty($filters['search'])) {
            $sql .= " AND mb.namabarang LIKE ?";
            $params[] = "%" . $filters['search'] . "%";
        }

        // Add sorting
        $sortBy = $filters['sort_by'] ?? 'namabarang';
        $sortOrder = $filters['sort_order'] ?? 'ASC';
        
        // Map sort columns to actual table columns
        $sortColumn = $sortBy === 'namapabrik' ? 'tp.namapabrik' : 'mb.namabarang';
        
        $sql .= " ORDER BY {$sortColumn} {$sortOrder}";

        return $this->db->fetchAll($sql, $params);
    }

    private function exportExcelBarangTidakTerjual($data, $filters) {
        $filename = 'Laporan_Barang_Tidak_Terjual_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Add BOM for UTF-8 to ensure Excel displays correctly
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['No', 'Nama Barang', 'Satuan', 'Pabrik', 'Stok'], ';');

        // Data
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['namabarang'] ?? '-',
                $row['satuan'] ?? '-',
                $row['namapabrik'] ?? '-',
                $row['stokakhir'] ?? '0'
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFBarangTidakTerjual($data, $filters) {
        $this->generateAndDownloadPDFBarangTidakTerjual($data, $filters);
    }

    private function generateAndDownloadPDFBarangTidakTerjual($data, $filters) {
        $filename = 'Laporan_Barang_Tidak_Terjual_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('P', 'mm', 'A4');
        $pdf->reportTitle = 'Laporan Barang Tidak Terjual';
        
        // Buat subtitle periode
        $periodeText = '';
        if ($filters['periode'] === 'today') {
            $periodeText = 'Hari Ini (' . date('d/m/Y') . ')';
        } elseif ($filters['periode'] === 'this_month') {
            $periodeText = 'Bulan Ini (' . date('F Y') . ')';
        } elseif ($filters['periode'] === 'this_year') {
            $periodeText = 'Tahun Ini (' . date('Y') . ')';
        } elseif ($filters['periode'] === 'custom') {
            $periodeText = 'Periode: ' . $filters['start_date'] . ' s/d ' . $filters['end_date'];
        }
        
        $pdf->reportSubtitle = $periodeText . "\nTotal Item: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        $header = ['No', 'Nama Barang', 'Satuan', 'Pabrik', 'Stok'];
        // Sesuaikan lebar kolom agar pas A4 (kurang lebih 190mm usable width)
        // 10 + 80 + 20 + 50 + 30 = 190
        $widths = [10, 80, 25, 45, 30];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($d['namabarang'] ?? '-', 0, 45), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[3], 6, substr($d['namapabrik'] ?? '-', 0, 25), 1, 0, 'L');
            $pdf->Cell($widths[4], 6, number_format((float)($d['stokakhir'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('D', $filename);
    }

    private function exportExcelDistribusiPenjualan($data, $filters) {
        $filename = 'Laporan_Distribusi_Penjualan_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // BOM

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['No', 'Sales', 'Customer', 'Barang', 'Satuan', 'Pabrik', 'Qty', 'Total Nilai', 'Rata-rata'], ';');

        // Data
        $no = 1;
        foreach ($data as $row) {
            fputcsv($output, [
                $no++,
                $row['namasales'] ?? '-',
                $row['namacustomer'] ?? '-',
                $row['namabarang'] ?? '-',
                $row['satuan'] ?? '-',
                $row['namapabrik'] ?? '-',
                number_format((float)($row['total_jumlah'] ?? 0), 0, ',', '.'),
                number_format((float)($row['total_nilai'] ?? 0), 0, ',', '.'),
                number_format((float)($row['harga_rata_rata'] ?? 0), 0, ',', '.')
            ], ';');
        }

        fclose($output);
    }

    private function exportPDFDistribusiPenjualan($data, $filters) {
        $this->generateAndDownloadPDFDistribusiPenjualan($data, $filters);
    }

    private function generateAndDownloadPDFDistribusiPenjualan($data, $filters) {
        $filename = 'Laporan_Distribusi_Penjualan_' . date('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = new LaporanPDF('L', 'mm', 'A4'); // Landscape for more columns
        $pdf->reportTitle = 'Laporan Distribusi Penjualan';
        
        // Buat subtitle periode
        $periodeText = '';
        if ($filters['periode'] === 'today') {
            $periodeText = 'Hari Ini (' . date('d/m/Y') . ')';
        } elseif ($filters['periode'] === 'this_month') {
            $periodeText = 'Bulan Ini (' . date('F Y') . ')';
        } elseif ($filters['periode'] === 'this_year') {
            $periodeText = 'Tahun Ini (' . date('Y') . ')';
        } elseif ($filters['periode'] === 'custom') {
            $periodeText = 'Periode: ' . $filters['start_date'] . ' s/d ' . $filters['end_date'];
        }
        
        $pdf->reportSubtitle = $periodeText . "\nTotal Data: " . count($data);
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 10 + 35 + 50 + 60 + 20 + 20 + 35 + 30 = 260 (A4 Landscape usable ~270-280)
        $header = ['No', 'Sales', 'Customer', 'Barang', 'Satuan', 'Qty', 'Total', 'Avg'];
        $widths = [10, 35, 50, 60, 20, 20, 35, 30];

        $pdf->TableHeader($header, $widths);

        $pdf->SetFont('Helvetica', '', 8);
        $no = 1;

        foreach ($data as $d) {
            $pdf->Cell($widths[0], 6, $no++, 1, 0, 'C');
            $pdf->Cell($widths[1], 6, substr($d['namasales'] ?? '-', 0, 20), 1, 0, 'L');
            $pdf->Cell($widths[2], 6, substr($d['namacustomer'] ?? '-', 0, 25), 1, 0, 'L');
            $pdf->Cell($widths[3], 6, substr($d['namabarang'] ?? '-', 0, 35), 1, 0, 'L');
            $pdf->Cell($widths[4], 6, $d['satuan'] ?? '-', 1, 0, 'C');
            $pdf->Cell($widths[5], 6, number_format((float)($d['total_jumlah'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[6], 6, number_format((float)($d['total_nilai'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell($widths[7], 6, number_format((float)($d['harga_rata_rata'] ?? 0), 0, ',', '.'), 1, 0, 'R');
            $pdf->Ln();
        }

        $pdf->Output('D', $filename);
    }

    public function customerNonAktif() {
        Auth::requireRole(['admin', 'manajemen', 'operator', 'sales']);

        $search = trim($_GET['search'] ?? '');
        $kodesales = trim($_GET['kodesales'] ?? '');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $export = $_GET['export'] ?? '';
        
        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPageOptions = [10, 25, 50, 100, 200, 500, 1000];
        $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
        $perPage = in_array($perPage, $perPageOptions, true) ? $perPage : 50;

        // Calculate the 6 months header
        $monthHeaders = [];
        $targetDate = new DateTime("$year-$month-01");
        
        // We want the 6 months BEFORE the target date.
        $startDate = clone $targetDate;
        $startDate->modify('-6 months');
        
        $monthKeys = [];
        for ($i = 0; $i < 6; $i++) {
            $currentMonth = clone $startDate;
            $currentMonth->modify("+$i months");
            $monthHeaders[] = [
                'name' => $currentMonth->format('F'), 
                'year' => $currentMonth->format('Y'),
                'key' => $currentMonth->format('Y-m')
            ];
            $monthKeys[] = $currentMonth->format('Y-m');
        }

        // Handle export
        if (!empty($export)) {
            $allData = $this->getAllCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys);
            $totals = $this->getTotalsCustomerNonAktif($search, $kodesales, $month, $year, $monthKeys);
            
            if ($export === 'excel') {
                $this->exportExcelCustomerNonAktif($allData, $totals, $monthHeaders);
            } elseif ($export === 'pdf') {
                $this->exportPDFCustomerNonAktif($allData, $totals, $monthHeaders, $month, $year);
            }
            exit;
        }

        // Restriction for Sales role
        if (Auth::isSales()) {
            $user = Auth::user();
            $kodesales = $user['kodesales'] ?? '';
        }

        $reportData = $this->getCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys, $page, $perPage);
        $total = $this->countCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys);
        $totalPages = $perPage > 0 ? (int)ceil($total / $perPage) : 1;
        
        $salesList = $this->salesModel->getAllActive();
        if (Auth::isSales()) {
             // Filter salesList to only include the current sales user
             $user = Auth::user();
             $myKodesales = $user['kodesales'] ?? '';
             $salesList = array_filter($salesList, function($s) use ($myKodesales) {
                 return $s['kodesales'] === $myKodesales;
             });
        }
        
        $totals = $this->getTotalsCustomerNonAktif($search, $kodesales, $month, $year, $monthKeys);

        $data = [
            'reportData' => $reportData,
            'page' => $page,
            'perPage' => $perPage,
            'perPageOptions' => $perPageOptions,
            'total' => $total,
            'totalPages' => $totalPages,
            'search' => $search,
            'kodesales' => $kodesales,
            'month' => $month,
            'year' => $year,
            'salesList' => $salesList,
            'monthHeaders' => $monthHeaders,
            'totals' => $totals,
            'isSales' => Auth::isSales()
        ];

        $this->view('laporan/customer-non-aktif', $data);
    }

    private function getAllCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys) {
        $startDate = $monthKeys[0] . '-01';
        $endDate = date('Y-m-t', strtotime($monthKeys[5] . '-01'));
        
        $where = ["hp.tanggalpenjualan BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        
        // Exclude customers active in target month
        $excludeSql = "SELECT DISTINCT sub_hp.kodecustomer 
                       FROM headerpenjualan sub_hp 
                       WHERE YEAR(sub_hp.tanggalpenjualan) = ? AND MONTH(sub_hp.tanggalpenjualan) = ?";
        $params[] = $year;
        $params[] = $month;
        
        $where[] = "hp.kodecustomer NOT IN ($excludeSql)";
        
        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR ms.namasales LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $selects = ["mc.namacustomer"];
        foreach ($monthKeys as $index => $key) {
            $i = $index + 1;
            $selects[] = "SUM(CASE WHEN DATE_FORMAT(hp.tanggalpenjualan, '%Y-%m') = '$key' THEN hp.nilaipenjualan ELSE 0 END) as month$i";
        }
        $selectClause = implode(', ', $selects);
        
        $sql = "SELECT $selectClause
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
                WHERE $whereClause
                GROUP BY hp.kodecustomer, mc.namacustomer
                ORDER BY mc.namacustomer ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    private function exportExcelCustomerNonAktif($data, $totals, $monthHeaders) {
        $filename = 'Laporan_Customer_Non_Aktif_' . date('YmdHis') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo "\xEF\xBB\xBF"; // UTF-8 BOM

        $output = fopen('php://output', 'w');

        // Header Rows
        $headerRow1 = ['Nama Customer'];
        foreach ($monthHeaders as $mHeader) {
            $headerRow1[] = $mHeader['name'] . ' ' . $mHeader['year'];
        }
        fputcsv($output, $headerRow1, ';');

        // Data
        foreach ($data as $row) {
            $csvRow = [$row['namacustomer'] ?? '-'];
            for ($i = 1; $i <= 6; $i++) {
                $csvRow[] = $row['month' . $i] ?? 0;
            }
            fputcsv($output, $csvRow, ';');
        }

        // Total Row
        $totalRow = ['TOTAL'];
        for ($i = 1; $i <= 6; $i++) {
            $totalRow[] = $totals['month' . $i] ?? 0;
        }
        fputcsv($output, $totalRow, ';');

        fclose($output);
    }

    private function exportPDFCustomerNonAktif($data, $totals, $monthHeaders, $month, $year) {
        $filename = 'Laporan_Customer_Non_Aktif_' . date('Y-m-d_H-i-s') . '.pdf';

        $pdf = new LaporanPDF('L', 'mm', 'A4'); // Landscape
        $pdf->reportTitle = 'Laporan Customer Non Aktif';
        
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $periodeText = 'Periode: ' . $months[$month] . ' ' . $year;
        $pdf->reportSubtitle = $periodeText;
        $pdf->printedBy = Auth::user()['namalengkap'] ?? 'System';
        
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Header Structure
        $header = ['Nama Customer'];
        foreach ($monthHeaders as $mHeader) {
            $header[] = substr($mHeader['name'], 0, 3) . ' ' . substr($mHeader['year'], 2);
        }
        
        // Widths: A4 Landscape width ~277mm usable. 
        // 7 columns (1 name + 6 months). 
        // 80 for name + 6 * 30 = 260. Plus logic for margins.
        $w = [85, 30, 30, 30, 30, 30, 30]; 
        
        $pdf->TableHeader($header, $w);

        $pdf->SetFont('Helvetica', '', 9);
        
        foreach ($data as $row) {
             // Check page break
            if ($pdf->GetY() > 180) {
                $pdf->AddPage();
                $pdf->TableHeader($header, $w);
                $pdf->SetFont('Helvetica', '', 9);
            }
            
            $pdf->Cell($w[0], 7, substr($row['namacustomer'], 0, 40), 1, 0, 'L');
            for ($i = 1; $i <= 6; $i++) {
                $pdf->Cell($w[$i], 7, number_format((float)($row['month' . $i] ?? 0), 0, ',', '.'), 1, 0, 'R');
            }
            $pdf->Ln();
        }

        // Total
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->Cell($w[0], 7, 'TOTAL', 1, 0, 'R');
        for ($i = 1; $i <= 6; $i++) {
             $pdf->Cell($w[$i], 7, number_format((float)($totals['month' . $i] ?? 0), 0, ',', '.'), 1, 0, 'R');
        }
        $pdf->Ln();

        $pdf->Output('D', $filename);
    }
    
    private function getCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys, $page, $perPage) {
        $offset = ($page - 1) * $perPage;
        
        // Window Range
        $startDate = $monthKeys[0] . '-01';
        $endDate = date('Y-m-t', strtotime($monthKeys[5] . '-01'));
        
        $where = ["hp.tanggalpenjualan BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        
        // Exclude customers active in target month
        $excludeSql = "SELECT DISTINCT sub_hp.kodecustomer 
                       FROM headerpenjualan sub_hp 
                       WHERE YEAR(sub_hp.tanggalpenjualan) = ? AND MONTH(sub_hp.tanggalpenjualan) = ?";
        $params[] = $year;
        $params[] = $month;
        
        $where[] = "hp.kodecustomer NOT IN ($excludeSql)";
        
        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR ms.namasales LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Dynamic Sum Case columns
        $selects = ["mc.namacustomer"];
        foreach ($monthKeys as $index => $key) {
            $i = $index + 1;
            $selects[] = "SUM(CASE WHEN DATE_FORMAT(hp.tanggalpenjualan, '%Y-%m') = '$key' THEN hp.nilaipenjualan ELSE 0 END) as month$i";
        }
        $selectClause = implode(', ', $selects);
        
        $sql = "SELECT $selectClause
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
                WHERE $whereClause
                GROUP BY hp.kodecustomer, mc.namacustomer
                ORDER BY mc.namacustomer ASC
                LIMIT ? OFFSET ?";
        
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    private function countCustomerNonAktifData($search, $kodesales, $month, $year, $monthKeys) {
        // Window Range
        $startDate = $monthKeys[0] . '-01';
        $endDate = date('Y-m-t', strtotime($monthKeys[5] . '-01'));
        
        $where = ["hp.tanggalpenjualan BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        
        // Exclude customers active in target month
        $excludeSql = "SELECT DISTINCT sub_hp.kodecustomer 
                       FROM headerpenjualan sub_hp 
                       WHERE YEAR(sub_hp.tanggalpenjualan) = ? AND MONTH(sub_hp.tanggalpenjualan) = ?";
        $params[] = $year;
        $params[] = $month;
        
        $where[] = "hp.kodecustomer NOT IN ($excludeSql)";
        
        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR ms.namasales LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT COUNT(DISTINCT hp.kodecustomer) as total
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
                WHERE $whereClause";
                
        $result = $this->db->fetchOne($sql, $params);
        return $result['total'] ?? 0;
    }

    private function getTotalsCustomerNonAktif($search, $kodesales, $month, $year, $monthKeys) {
        // Window Range
        $startDate = $monthKeys[0] . '-01';
        $endDate = date('Y-m-t', strtotime($monthKeys[5] . '-01'));
        
        $where = ["hp.tanggalpenjualan BETWEEN ? AND ?"];
        $params = [$startDate, $endDate];
        
        // Exclude customers active in target month
        $excludeSql = "SELECT DISTINCT sub_hp.kodecustomer 
                       FROM headerpenjualan sub_hp 
                       WHERE YEAR(sub_hp.tanggalpenjualan) = ? AND MONTH(sub_hp.tanggalpenjualan) = ?";
        $params[] = $year;
        $params[] = $month;
        
        $where[] = "hp.kodecustomer NOT IN ($excludeSql)";
        
        if (!empty($search)) {
            $where[] = "(mc.namacustomer LIKE ? OR ms.namasales LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (!empty($kodesales)) {
            $where[] = "hp.kodesales = ?";
            $params[] = $kodesales;
        }
        
        $whereClause = implode(' AND ', $where);
        
        // Dynamic Sum Case columns
        $selects = [];
        foreach ($monthKeys as $index => $key) {
            $i = $index + 1;
            $selects[] = "SUM(CASE WHEN DATE_FORMAT(hp.tanggalpenjualan, '%Y-%m') = '$key' THEN hp.nilaipenjualan ELSE 0 END) as month$i";
        }
        $selectClause = implode(', ', $selects);
        
        $sql = "SELECT $selectClause
                FROM headerpenjualan hp
                JOIN mastercustomer mc ON hp.kodecustomer = mc.kodecustomer
                LEFT JOIN mastersales ms ON hp.kodesales = ms.kodesales
                WHERE $whereClause";
        
        return $this->db->fetchOne($sql, $params);
    }
}
