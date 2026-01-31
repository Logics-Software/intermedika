<?php
class ApiOmsetController extends Controller {
    private $omsetModel;

    public function __construct() {
        parent::__construct();
        $this->omsetModel = new Omset();
    }

    public function omset() {
        // Simple API without token authentication for VB6 bridging
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Handle method override for PUT/DELETE
        if (isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }
        
        if ($method === 'GET') {
            $this->findOmset();
        } elseif ($method === 'POST') {
            $this->addOmset();
        } elseif ($method === 'DELETE') {
            $this->deleteOmset();
        } else {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function findOmset() {
        $tahun = $_GET['tahun'] ?? null;
        $bulan = $_GET['bulan'] ?? null;
        $kodesales = $_GET['kodesales'] ?? null;

        if (!$tahun || !$bulan || !$kodesales) {
            $this->json([
                'success' => false,
                'message' => 'Parameter tahun, bulan, dan kodesales harus diisi'
            ], 400);
            return;
        }

        $omset = $this->omsetModel->findByKey($tahun, $bulan, $kodesales);
        
        if ($omset) {
            $this->json([
                'success' => true,
                'data' => $omset
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Data omset tidak ditemukan'
            ], 404);
        }
    }

    private function addOmset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        // Validate required fields
        $required = ['tahun', 'bulan', 'kodesales'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "Field {$field} is required"
                ], 400);
                return;
            }
        }

        // Check if record already exists
        $existing = $this->omsetModel->findByKey(
            $input['tahun'],
            $input['bulan'],
            $input['kodesales']
        );

        if ($existing) {
            // Update existing record
            $this->json([
                'success' => false,
                'message' => 'Data omset sudah ada. Gunakan update atau delete terlebih dahulu.'
            ], 400);
            return;
        }

        // Prepare data
        $data = [
            'tahun' => $input['tahun'],
            'bulan' => $input['bulan'],
            'kodesales' => $input['kodesales'],
            'namasales' => $input['namasales'] ?? '',
            'jumlahfaktur' => isset($input['jumlahfaktur']) ? (float)$input['jumlahfaktur'] : 0,
            'penjualan' => isset($input['penjualan']) ? (float)$input['penjualan'] : 0,
            'returpenjualan' => isset($input['returpenjualan']) ? (float)$input['returpenjualan'] : 0,
            'penjualanbersih' => isset($input['penjualanbersih']) ? (float)$input['penjualanbersih'] : 0,
            'targetpenjualan' => isset($input['targetpenjualan']) ? (float)$input['targetpenjualan'] : 0,
            'prosenpenjualan' => isset($input['prosenpenjualan']) ? (float)$input['prosenpenjualan'] : 0,
            'penerimaantunai' => isset($input['penerimaantunai']) ? (float)$input['penerimaantunai'] : 0,
            'cnpenjualan' => isset($input['cnpenjualan']) ? (float)$input['cnpenjualan'] : 0,
            'pencairangiro' => isset($input['pencairangiro']) ? (float)$input['pencairangiro'] : 0,
            'penerimaanbersih' => isset($input['penerimaanbersih']) ? (float)$input['penerimaanbersih'] : 0,
            'targetpenerimaan' => isset($input['targetpenerimaan']) ? (float)$input['targetpenerimaan'] : 0,
            'prosenpenerimaan' => isset($input['prosenpenerimaan']) ? (float)$input['prosenpenerimaan'] : 0
        ];

        try {
            $id = $this->omsetModel->create($data);
            $omset = $this->omsetModel->findByKey($data['tahun'], $data['bulan'], $data['kodesales']);
            
            $this->json([
                'success' => true,
                'message' => 'Data omset berhasil ditambahkan',
                'data' => $omset
            ], 201);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menambahkan data omset: ' . $e->getMessage()
            ], 500);
        }
    }

    private function deleteOmset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        // For DELETE method, also check GET parameters
        if (empty($input['tahun'])) {
            $input['tahun'] = $_GET['tahun'] ?? null;
        }
        if (empty($input['bulan'])) {
            $input['bulan'] = $_GET['bulan'] ?? null;
        }

        $tahun = $input['tahun'] ?? null;
        $bulan = $input['bulan'] ?? null;

        if (!$tahun || !$bulan) {
            $this->json([
                'success' => false,
                'message' => 'Parameter tahun dan bulan harus diisi untuk delete'
            ], 400);
            return;
        }

        try {
            $deleted = $this->omsetModel->deleteByTahunBulan($tahun, $bulan);
            
            if ($deleted > 0) {
                $this->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deleted} record omset untuk tahun {$tahun} bulan {$bulan}"
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Data omset tidak ditemukan untuk dihapus'
                ], 404);
            }
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menghapus data omset: ' . $e->getMessage()
            ], 500);
        }
    }
}

