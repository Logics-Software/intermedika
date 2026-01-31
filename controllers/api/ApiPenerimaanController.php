<?php
class ApiPenerimaanController extends Controller {
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $input = $this->getInputData();
        $action = $input['action'] ?? $_POST['action'] ?? $_GET['action'] ?? null;

        // Handle update_status action via POST, PUT, or PATCH (for VB6 compatibility)
        if ($action === 'update_status' && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $this->updateStatus();
            return;
        }

        switch ($method) {
            case 'GET':
                $this->getPenerimaan();
                break;
            case 'POST':
                $this->createPenerimaan();
                break;
            case 'PUT':
            case 'PATCH':
                $this->updatePenerimaan($method);
                break;
            case 'DELETE':
                $this->deletePenerimaan();
                break;
            default:
                $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function getPenerimaan() {
        $nopenerimaan = $_GET['nopenerimaan'] ?? null;
        $headerModel = new Headerpenerimaan();
        $detailModel = new Detailpenerimaan();

        if ($nopenerimaan) {
            $header = $headerModel->findByNopenerimaan($nopenerimaan);
            if (!$header) {
                $this->json(['success' => false, 'message' => 'Penerimaan not found'], 404);
                return;
            }
            
            // Jika ada user yang login dan role adalah sales, pastikan hanya bisa melihat penerimaan mereka sendiri
            if (Auth::check() && Auth::isSales()) {
                $currentUser = Auth::user();
                if (!empty($currentUser['kodesales']) && $header['kodesales'] !== $currentUser['kodesales']) {
                    $this->json(['success' => false, 'message' => 'Access denied'], 403);
                    return;
                }
            }
            
            $details = $detailModel->getByNopenerimaan($nopenerimaan);
            $header['details'] = $details;
            $this->json(['success' => true, 'data' => $header]);
            return;
        }

        $page = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
        $perPage = isset($_GET['per_page']) ? max((int)$_GET['per_page'], 1) : 20;
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? 'belumproses'; // Default filter: status = 'belumproses'
        $kodecustomer = $_GET['kodecustomer'] ?? null;
        $kodesales = $_GET['kodesales'] ?? null;
        $startDate = $_GET['start_date'] ?? null;
        $endDate = $_GET['end_date'] ?? null;

        $options = [
            'page' => $page,
            'per_page' => $perPage,
            'search' => $search,
            'status' => $status,
            'kodecustomer' => $kodecustomer,
            'kodesales' => $kodesales,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        // Jika ada user yang login dan role adalah sales, filter hanya data penerimaan dari sales tersebut
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

    private function createPenerimaan() {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }

        $required = [
            'nopenerimaan',
            'tanggalpenerimaan',
            'jenispenerimaan'
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

        $headerModel = new Headerpenerimaan();
        if ($headerModel->findByNopenerimaan($input['nopenerimaan'])) {
            $this->json(['success' => false, 'message' => 'nopenerimaan already exists'], 409);
            return;
        }

        try {
            $headerModel->create($this->buildHeaderData($input), $this->buildDetailData($input['details']));
            $created = $headerModel->findByNopenerimaan($input['nopenerimaan']);
            $details = (new Detailpenerimaan())->getByNopenerimaan($input['nopenerimaan']);
            $created['details'] = $details;

            $this->json(['success' => true, 'message' => 'Penerimaan created', 'data' => $created], 201);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to create penerimaan', 'error' => $e->getMessage()], 500);
        }
    }

    private function updatePenerimaan($method) {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }

        $nopenerimaan = $input['nopenerimaan'] ?? $_GET['nopenerimaan'] ?? null;
        if (!$nopenerimaan) {
            $this->json(['success' => false, 'message' => 'nopenerimaan is required'], 400);
            return;
        }

        $headerModel = new Headerpenerimaan();
        $existing = $headerModel->findByNopenerimaan($nopenerimaan);
        if (!$existing) {
            $this->json(['success' => false, 'message' => 'Penerimaan not found'], 404);
            return;
        }

        if (!$headerModel->canEditOrDelete($nopenerimaan)) {
            $this->json(['success' => false, 'message' => 'Cannot edit penerimaan with status "proses"'], 400);
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
        unset($headerData['nopenerimaan']);

        try {
            if ($method === 'PUT') {
                $headerModel->update($nopenerimaan, $headerData, $details);
            } else {
                // PATCH
                if (!empty($headerData)) {
                    $headerModel->patch($nopenerimaan, $headerData);
                }
                if (is_array($details)) {
                    $headerModel->update($nopenerimaan, [], $details);
                }
            }

            $updated = $headerModel->findByNopenerimaan($nopenerimaan);
            $detailData = (new Detailpenerimaan())->getByNopenerimaan($nopenerimaan);
            $updated['details'] = $detailData;
            $this->json(['success' => true, 'message' => 'Penerimaan updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update penerimaan', 'error' => $e->getMessage()], 500);
        }
    }

    private function deletePenerimaan() {
        $input = $this->getInputData();
        $nopenerimaan = $input['nopenerimaan'] ?? $_POST['nopenerimaan'] ?? $_GET['nopenerimaan'] ?? null;
        if (!$nopenerimaan) {
            $this->json(['success' => false, 'message' => 'nopenerimaan is required'], 400);
            return;
        }

        $headerModel = new Headerpenerimaan();
        if (!$headerModel->findByNopenerimaan($nopenerimaan)) {
            $this->json(['success' => false, 'message' => 'Penerimaan not found'], 404);
            return;
        }

        if (!$headerModel->canEditOrDelete($nopenerimaan)) {
            $this->json(['success' => false, 'message' => 'Cannot delete penerimaan with status "proses"'], 400);
            return;
        }

        try {
            $headerModel->delete($nopenerimaan);
            $this->json(['success' => true, 'message' => 'Penerimaan deleted']);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to delete penerimaan', 'error' => $e->getMessage()], 500);
        }
    }

    private function updateStatus() {
        $input = $this->getInputData();
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid payload'], 400);
            return;
        }
        
        $nopenerimaan = $input['nopenerimaan'] ?? null;
        $status = $input['status'] ?? null;
        // Get noinkaso - if key exists, use it (even if empty string), otherwise null
        $noinkaso = array_key_exists('noinkaso', $input) ? $input['noinkaso'] : null;

        if (!$nopenerimaan) {
            $this->json(['success' => false, 'message' => 'nopenerimaan is required'], 400);
            return;
        }

        if (!$status || !in_array($status, ['belumproses', 'proses'], true)) {
            $this->json(['success' => false, 'message' => 'status must be "belumproses" or "proses"'], 400);
            return;
        }

        $headerModel = new Headerpenerimaan();
        if (!$headerModel->findByNopenerimaan($nopenerimaan)) {
            $this->json(['success' => false, 'message' => 'Penerimaan not found'], 404);
            return;
        }

        try {
            // Always pass noinkaso (will be normalized in the model)
            $headerModel->updateStatusAndNoinkaso($nopenerimaan, $status, $noinkaso);
            $updated = $headerModel->findByNopenerimaan($nopenerimaan);
            $this->json(['success' => true, 'message' => 'Status updated', 'data' => $updated]);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to update status', 'error' => $e->getMessage()], 500);
        }
    }

    private function buildHeaderData(array $input, $partial = false) {
        $fields = [
            'nopenerimaan' => null,
            'tanggalpenerimaan' => null,
            'statuspkp' => null,
            'jenispenerimaan' => null,
            'kodesales' => null,
            'kodecustomer' => null,
            'totalpiutang' => null,
            'totalpotongan' => null,
            'totallainlain' => null,
            'totalnetto' => null,
            'status' => null,
            'noinkaso' => null,
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
            if (in_array($field, ['totalpiutang', 'totalpotongan', 'totallainlain', 'totalnetto'], true)) {
                if ($value === '' || $value === null) {
                    if (!$partial) {
                        $result[$field] = 0;
                    }
                } else {
                    $result[$field] = $this->toDecimal($value);
                }
            } elseif ($field === 'statuspkp') {
                if ($value !== '' && $value !== null) {
                    if (!in_array($value, ['pkp', 'nonpkp'], true)) {
                        throw new InvalidArgumentException('statuspkp must be either "pkp" or "nonpkp"');
                    }
                    $result[$field] = $value;
                } elseif (!$partial) {
                    $result[$field] = null;
                }
            } elseif ($field === 'jenispenerimaan') {
                if ($value !== '' && $value !== null) {
                    if (!in_array($value, ['tunai', 'transfer', 'giro'], true)) {
                        throw new InvalidArgumentException('jenispenerimaan must be "tunai", "transfer", or "giro"');
                    }
                    $result[$field] = $value;
                }
            } elseif ($field === 'status') {
                if ($value !== '' && $value !== null) {
                    if (!in_array($value, ['belumproses', 'proses'], true)) {
                        throw new InvalidArgumentException('status must be "belumproses" or "proses"');
                    }
                    $result[$field] = $value;
                } elseif (!$partial) {
                    $result[$field] = 'belumproses';
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
            if (empty($detail['nopenjualan'])) {
                throw new InvalidArgumentException('Detail requires nopenjualan');
            }
            $nogiro = $detail['nogiro'] ?? null;
            $tanggalcair = $detail['tanggalcair'] ?? null;
            
            // Convert empty strings to null for database compatibility
            if ($nogiro === '') {
                $nogiro = null;
            }
            if ($tanggalcair === '') {
                $tanggalcair = null;
            }
            
            $formatted[] = [
                'nopenjualan' => $detail['nopenjualan'],
                'nogiro' => $nogiro,
                'tanggalcair' => $tanggalcair,
                'piutang' => $this->toDecimal($detail['piutang'] ?? 0),
                'potongan' => $this->toDecimal($detail['potongan'] ?? 0),
                'lainlain' => $this->toDecimal($detail['lainlain'] ?? 0),
                'netto' => $this->toDecimal($detail['netto'] ?? 0),
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
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } elseif ($hasComma) {
                $value = str_replace(',', '.', $value);
            } else {
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

