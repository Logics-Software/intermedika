<?php
class ApiPenjualanController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Get action from POST, GET, or JSON body
        $action = $_POST['action'] ?? $_GET['action'] ?? null;
        
        // If action not found in POST/GET, try to read from JSON body
        if (!$action && $method === 'POST') {
            $rawInput = $GLOBALS['_RAW_INPUT'] ?? file_get_contents('php://input');
            if (!empty($rawInput)) {
                $jsonInput = json_decode($rawInput, true);
                if ($jsonInput && isset($jsonInput['action'])) {
                    $action = $jsonInput['action'];
                }
            }
        }

        if ($action === 'update_saldo' && $method === 'POST') {
            $this->updateSaldo();
            return;
        }

        if ($action === 'update_order_info' && $method === 'POST') {
            $this->updateOrderInfo();
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getPenjualan();
                break;
            case 'POST':
                $this->createPenjualan();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updatePenjualan($method);
                break;
            case 'DELETE':
                $this->deletePenjualan();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getPenjualan() {
        $nopenjualan = $_GET['nopenjualan'] ?? null;
        $headerModel = new Headerpenjualan();
        $detailModel = new Detailpenjualan();

        if ($nopenjualan) {
            $header = $headerModel->findByNopenjualan($nopenjualan);
            if (!$header) {
                $this->json(['success' => false, 'message' => 'Penjualan not found'], 404);
                return;
            }
            
            // Jika ada user yang login dan role adalah sales, pastikan hanya bisa melihat penjualan mereka sendiri
            if (Auth::check() && Auth::isSales()) {
                $currentUser = Auth::user();
                if (!empty($currentUser['kodesales']) && $header['kodesales'] !== $currentUser['kodesales']) {
                    $this->json(['success' => false, 'message' => 'Access denied'], 403);
                    return;
                }
            }
            
            $details = $detailModel->getByNopenjualan($nopenjualan);
            $header['details'] = $details;
            $this->json(['success' => true, 'data' => $header]);
            return;
        }

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? max((int)$_GET['per_page'], 1) : 20;
        $search = $_GET['search'] ?? '';
        $kodesales = $_GET['kodesales'] ?? null;
        $periode = $_GET['periode'] ?? 'today';
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;
        $statuspkp = $_GET['statuspkp'] ?? null;

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'kodesales' => $kodesales,
            'periode' => $periode,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'statuspkp' => $statuspkp
        ];

        // Jika ada user yang login dan role adalah sales, filter hanya data penjualan dari sales tersebut
        if (Auth::check() && Auth::isSales()) {
            $currentUser = Auth::user();
            if (!empty($currentUser['kodesales'])) {
                // Override kodesales dari parameter jika user adalah sales
                $options['kodesales'] = $currentUser['kodesales'];
            }
        }

        $data = $headerModel->getAll($options);
        $total = $headerModel->count($options);

        $this->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => $perPage > 0 ? (int)ceil($total / $perPage) : 1
            ]
        ]);
    }

    private function createPenjualan() {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }

        $required = [
            'nopenjualan',
            'tanggalpenjualan',
            'kodeformulir',
            'kodecustomer',
            'kodesales',
            'nilaipenjualan',
            'dpp',
            'ppn',
            'saldopenjualan',
            'userid'
        ];

        foreach ($required as $field) {
            if (!isset($input[$field]) || $input[$field] === '') {
                $this->json(['success' => false, 'message' => "Field {$field} is required"], 400);
                return;
            }
        }

        if (empty($input['details']) || !is_array($input['details'])) {
            $this->json(['success' => false, 'message' => 'Details are required'], 400);
            return;
        }

        $headerModel = new Headerpenjualan();
        if ($headerModel->findByNopenjualan($input['nopenjualan'])) {
            $this->json(['success' => false, 'message' => 'nopenjualan already exists'], 409);
            return;
        }

        try {
            $headerModel->create($this->buildHeaderData($input), $this->buildDetailData($input['details']));
            $created = $headerModel->findByNopenjualan($input['nopenjualan']);
            $details = (new Detailpenjualan())->getByNopenjualan($input['nopenjualan']);
            $created['details'] = $details;

            $this->json(['success' => true, 'message' => 'Penjualan created', 'data' => $created], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create penjualan', 'error' => $e->getMessage()], 500);
        }
    }

    private function updatePenjualan($method) {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }

        $nopenjualan = $input['nopenjualan'] ?? $_GET['nopenjualan'] ?? null;
        if (!$nopenjualan) {
            $this->json(['success' => false, 'message' => 'nopenjualan is required'], 400);
            return;
        }

        $headerModel = new Headerpenjualan();
        $existing = $headerModel->findByNopenjualan($nopenjualan);
        if (!$existing) {
            $this->json(['success' => false, 'message' => 'Penjualan not found'], 404);
            return;
        }

        $details = null;
        if (isset($input['details'])) {
            if (!is_array($input['details'])) {
                $this->json(['success' => false, 'message' => 'Details must be array'], 400);
                return;
            }
            $details = $this->buildDetailData($input['details']);
        }

        $headerData = $this->buildHeaderData($input, $method === 'PUT');
        unset($headerData['nopenjualan']);

        try {
            if ($method === 'PUT') {
                $headerModel->update($nopenjualan, $headerData, $details);
            } else {
                // PATCH
                if (!empty($headerData)) {
                    $headerModel->patch($nopenjualan, $headerData);
                }
                if (is_array($details)) {
                    $headerModel->update($nopenjualan, [], $details);
                }
            }

            $updated = $headerModel->findByNopenjualan($nopenjualan);
            $detailData = (new Detailpenjualan())->getByNopenjualan($nopenjualan);
            $updated['details'] = $detailData;
            $this->json(['success' => true, 'message' => 'Penjualan updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update penjualan', 'error' => $e->getMessage()], 500);
        }
    }

    private function deletePenjualan() {
        $input = $this->getInputData();
        $nopenjualan = $input['nopenjualan'] ?? $_POST['nopenjualan'] ?? $_GET['nopenjualan'] ?? null;
        if (!$nopenjualan) {
            $this->json(['success' => false, 'message' => 'nopenjualan is required'], 400);
            return;
        }

        $headerModel = new Headerpenjualan();
        if (!$headerModel->findByNopenjualan($nopenjualan)) {
            $this->json(['success' => false, 'message' => 'Penjualan not found'], 404);
            return;
        }

        try {
            $headerModel->delete($nopenjualan);
            $this->json(['success' => true, 'message' => 'Penjualan deleted']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete penjualan', 'error' => $e->getMessage()], 500);
        }
    }

    private function updateSaldo() {
        $input = $this->getInputData();
        $nopenjualan = $input['nopenjualan'] ?? null;
        $saldo = $input['saldopenjualan'] ?? null;

        if (!$nopenjualan || $saldo === null) {
            $this->json(['success' => false, 'message' => 'nopenjualan and saldopenjualan are required'], 400);
            return;
        }

        $headerModel = new Headerpenjualan();
        if (!$headerModel->findByNopenjualan($nopenjualan)) {
            $this->json(['success' => false, 'message' => 'Penjualan not found'], 404);
            return;
        }

        try {
            $headerModel->updateSaldo($nopenjualan, $this->toDecimal($saldo));
            $updated = $headerModel->findByNopenjualan($nopenjualan);
            $this->json(['success' => true, 'message' => 'Saldo updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update saldo', 'error' => $e->getMessage()], 500);
        }
    }

    private function updateOrderInfo() {
        $input = $this->getInputData();
        $nopenjualan = $input['nopenjualan'] ?? null;
        $noorder = $input['noorder'] ?? null;
        $tanggalorder = $input['tanggalorder'] ?? null;

        if (!$nopenjualan) {
            $this->json(['success' => false, 'message' => 'nopenjualan is required'], 400);
            return;
        }

        if (!$noorder || !$tanggalorder) {
            $this->json(['success' => false, 'message' => 'noorder and tanggalorder are required'], 400);
            return;
        }

        $headerModel = new Headerpenjualan();
        if (!$headerModel->findByNopenjualan($nopenjualan)) {
            $this->json(['success' => false, 'message' => 'Penjualan not found'], 404);
            return;
        }

        try {
            $headerModel->updateOrderInfo($nopenjualan, $noorder, $tanggalorder);
            $updated = $headerModel->findByNopenjualan($nopenjualan);
            $this->json(['success' => true, 'message' => 'Order info updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update order info', 'error' => $e->getMessage()], 500);
        }
    }

    private function buildHeaderData(array $input, $partial = false) {
        $fields = [
            'nopenjualan' => null,
            'tanggalpenjualan' => null,
            'statuspkp' => null,
            'kodeformulir' => null,
            'noorder' => null,
            'tanggalorder' => null,
            'tanggaljatuhtempo' => null,
            'keterangan' => null,
            'kodecustomer' => null,
            'kodesales' => null,
            'pengirim' => null,
            'dpp' => null,
            'ppn' => null,
            'nilaipenjualan' => null,
            'saldopenjualan' => null,
            'userid' => null,
        ];

        $result = [];

        foreach ($fields as $field => $default) {
            if (!array_key_exists($field, $input)) {
                if (!$partial && $default !== null) {
                    $result[$field] = $default;
                }
                continue;
            }

            $value = $input[$field];
            if (in_array($field, ['dpp', 'ppn', 'nilaipenjualan', 'saldopenjualan'], true)) {
                if ($value === '' || $value === null) {
                    if (!$partial) {
                        $result[$field] = 0;
                    }
                } else {
                    $result[$field] = $this->toDecimal($value);
                }
            } elseif ($field === 'statuspkp') {
                // Validasi enumerasi: hanya menerima "pkp" atau "nonpkp"
                if ($value !== '' && $value !== null) {
                    if (!in_array($value, ['pkp', 'nonpkp'], true)) {
                        throw new InvalidArgumentException('statuspkp must be either "pkp" or "nonpkp"');
                    }
                    $result[$field] = $value;
                } elseif (!$partial) {
                    $result[$field] = null;
                }
            } else {
                if ($value === '' && $partial) {
                    continue;
                }
                $result[$field] = $value;
            }
        }

        return $result;
    }

    private function buildDetailData(array $details) {
        $formatted = [];
        foreach ($details as $detail) {
            if (empty($detail['kodebarang'])) {
                throw new InvalidArgumentException('Detail requires kodebarang');
            }
            $formatted[] = [
                'kodebarang' => $detail['kodebarang'],
                'nopembelian' => $detail['nopembelian'] ?? null,
                'nomorbatch' => $detail['nomorbatch'] ?? null,
                'expireddate' => $detail['expireddate'] ?? null,
                'jumlah' => isset($detail['jumlah']) ? (int)$detail['jumlah'] : 0,
                'hargasatuan' => $this->toDecimal($detail['hargasatuan'] ?? 0),
                'discount' => isset($detail['discount']) ? (float)$detail['discount'] : 0,
                'jumlahharga' => $this->toDecimal($detail['jumlahharga'] ?? 0),
                'nourut' => isset($detail['nourut']) ? (int)$detail['nourut'] : null
            ];
        }
        return $formatted;
    }

    private function toDecimal($value) {
        if ($value === null || $value === '') {
            return 0;
        }
        if (is_string($value)) {
            $value = str_replace(' ', '', $value);
            $hasComma = strpos($value, ',') !== false;
            $hasDot = strpos($value, '.') !== false;

            if ($hasComma && $hasDot) {
                // Assume format like 1.234,56
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif ($hasComma) {
                // Assume comma as decimal separator
                $value = str_replace(',', '.', $value);
            } else {
                // Remove thousands separator commas if any
                $value = str_replace(',', '', $value);
            }
        }
        return (float)$value;
    }

    private function getInputData() {
        $raw = file_get_contents('php://input');
        if ($raw) {
            $json = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $json;
            }
            parse_str($raw, $parsed);
            if (!empty($parsed)) {
                return $parsed;
            }
        }
        if (!empty($_POST)) {
            return $_POST;
        }
        return null;
    }
}


