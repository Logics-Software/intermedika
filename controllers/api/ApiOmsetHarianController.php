<?php
class ApiOmsetHarianController extends Controller {
    private $omsetHarianModel;

    public function __construct() {
        parent::__construct();
        $this->omsetHarianModel = new OmsetHarian();
    }

    public function index() {
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
        } elseif ($method === 'PUT') {
            $this->updateOmset();
        } elseif ($method === 'DELETE') {
            $this->deleteOmset();
        } else {
            $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function findOmset() {
        $tanggal = $_GET['tanggal'] ?? null;
        $kodesales = $_GET['kodesales'] ?? null;

        if (!$tanggal || !$kodesales) {
            $this->json([
                'success' => false,
                'message' => 'Parameter tanggal dan kodesales harus diisi'
            ], 400);
            return;
        }

        $omset = $this->omsetHarianModel->findByKey($tanggal, $kodesales);
        
        if ($omset) {
            $this->json([
                'success' => true,
                'data' => $omset
            ]);
        } else {
            $this->json([
                'success' => false,
                'message' => 'Data omset harian tidak ditemukan'
            ], 404);
        }
    }

    private function addOmset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        // Validate required fields
        $required = ['tanggal', 'kodesales'];
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
        $existing = $this->omsetHarianModel->findByKey(
            $input['tanggal'],
            $input['kodesales']
        );

        if ($existing) {
            // Update existing record
            $this->json([
                'success' => false,
                'message' => 'Data omset harian sudah ada. Gunakan update atau delete terlebih dahulu.'
            ], 400);
            return;
        }

        // Prepare data
        $data = [
            'tanggal' => $input['tanggal'],
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
            $id = $this->omsetHarianModel->create($data);
            $omset = $this->omsetHarianModel->findByKey($data['tanggal'], $data['kodesales']);
            
            $this->json([
                'success' => true,
                'message' => 'Data omset harian berhasil ditambahkan',
                'data' => $omset
            ], 201);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menambahkan data omset harian: ' . $e->getMessage()
            ], 500);
        }
    }

    private function updateOmset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        // Validate key fields
        $required = ['tanggal', 'kodesales'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                $this->json([
                    'success' => false,
                    'message' => "Field {$field} is required for update"
                ], 400);
                return;
            }
        }

        // Check if record exists
        $existing = $this->omsetHarianModel->findByKey(
            $input['tanggal'],
            $input['kodesales']
        );

        if (!$existing) {
            $this->json([
                'success' => false,
                'message' => 'Data omset harian tidak ditemukan'
            ], 404);
            return;
        }

        // Prepare update data (only fields present in input)
        $updateData = [];
        $fields = [
            'namasales',
            'jumlahfaktur',
            'penjualan',
            'returpenjualan',
            'penjualanbersih',
            'targetpenjualan',
            'prosenpenjualan',
            'penerimaantunai',
            'cnpenjualan',
            'pencairangiro',
            'penerimaanbersih',
            'targetpenerimaan',
            'prosenpenerimaan'
        ];

        foreach ($fields as $field) {
            if (isset($input[$field])) {
                $updateData[$field] = $input[$field];
            }
        }

        try {
            $this->omsetHarianModel->update($input['tanggal'], $input['kodesales'], $updateData);
            
            $omset = $this->omsetHarianModel->findByKey($input['tanggal'], $input['kodesales']);
            
            $this->json([
                'success' => true,
                'message' => 'Data omset harian berhasil diupdate',
                'data' => $omset
            ]);
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal update data omset harian: ' . $e->getMessage()
            ], 500);
        }
    }

    private function deleteOmset() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $input = $_POST;
        }

        // For DELETE method, also check GET parameters
        if (empty($input['tanggal'])) {
            $input['tanggal'] = $_GET['tanggal'] ?? null;
        }

        $tanggal = $input['tanggal'] ?? null;

        if (!$tanggal) {
            $this->json([
                'success' => false,
                'message' => 'Parameter tanggal harus diisi untuk delete'
            ], 400);
            return;
        }

        try {
            $deleted = $this->omsetHarianModel->deleteByTanggal($tanggal);
            
            if ($deleted > 0) {
                $this->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$deleted} record omset harian untuk tanggal {$tanggal}"
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => 'Data omset harian tidak ditemukan untuk dihapus'
                ], 404);
            }
        } catch (Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Gagal menghapus data omset harian: ' . $e->getMessage()
            ], 500);
        }
    }
}
