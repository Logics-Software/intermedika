<?php
class ApiWebAuthnController extends Controller {
    private $webauthnModel;
    private $userModel;

    public function __construct() {
        $this->webauthnModel = new WebAuthnCredential();
        $this->userModel = new User();
        header('Content-Type: application/json');
    }

    // Start registration - get challenge
    public function registrationStart() {
        try {
            Session::start();
            
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['error' => 'User must be logged in to register biometric']);
                return;
            }

            $user = Auth::user();
            if (!$user) {
                http_response_code(401);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            $challenge = WebAuthn::generateChallenge();
            Session::set('webauthn_challenge', $challenge);
            Session::set('webauthn_challenge_time', time());

            $options = WebAuthn::createRegistrationOptions(
                $user['username'],
                (string)$user['id'],
                $challenge
            );

            echo json_encode(['success' => true, 'options' => $options]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Complete registration - save credential
    public function registrationComplete() {
        try {
            Session::start();

            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['error' => 'User must be logged in']);
                return;
            }

            $challenge = Session::get('webauthn_challenge');
            $challengeTime = Session::get('webauthn_challenge_time');

            // Verify challenge is valid and not expired (5 minutes)
            if (!$challenge || !$challengeTime || (time() - $challengeTime) > 300) {
                http_response_code(400);
                echo json_encode(['error' => 'Challenge expired or invalid']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['credential'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
                return;
            }

            $credential = $input['credential'];
            $credentialId = $credential['id'] ?? '';
            $publicKey = $credential['response']['publicKey'] ?? null;
            $attestationObject = $credential['response']['attestationObject'] ?? null;

            if (empty($credentialId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing credential ID']);
                return;
            }
            
            if (empty($attestationObject)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing attestation object']);
                return;
            }

            // Check if credential already exists
            $existing = $this->webauthnModel->findByCredentialId($credentialId);
            if ($existing) {
                http_response_code(400);
                echo json_encode(['error' => 'Credential already registered']);
                return;
            }

            $user = Auth::user();
            
            // Store credential (simplified - production should validate attestation)
            $publicKeyJson = json_encode([
                'type' => $credential['type'] ?? 'public-key',
                'id' => $credentialId,
                'publicKey' => $publicKey,
                'rawId' => $credential['rawId'] ?? null,
                'attestationObject' => $attestationObject
            ]);

            $this->webauthnModel->create([
                'user_id' => $user['id'],
                'credential_id' => $credentialId,
                'public_key' => $publicKeyJson,
                'counter' => 0,
                'aaguid' => null, // Will be extracted from attestation object in production
                'last_used_at' => null
            ]);

            // Clear challenge
            Session::remove('webauthn_challenge');
            Session::remove('webauthn_challenge_time');

            echo json_encode(['success' => true, 'message' => 'Biometric credential registered successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Start authentication - get challenge
    public function authenticationStart() {
        try {
            Session::start();

            $input = json_decode(file_get_contents('php://input'), true);
            $username = $input['username'] ?? '';

            if (empty($username)) {
                // Return success with empty credentials for checking purposes
                echo json_encode(['success' => true, 'options' => ['allowCredentials' => []]]);
                return;
            }

            $user = $this->userModel->findByUsername($username);
            if (!$user) {
                // Return success with empty credentials for checking purposes
                echo json_encode(['success' => true, 'options' => ['allowCredentials' => []]]);
                return;
            }

            $credentials = $this->webauthnModel->findByUserId($user['id']);
            
            if (empty($credentials)) {
                // Return success with empty credentials - allows frontend to check properly
                echo json_encode(['success' => true, 'options' => ['allowCredentials' => []]]);
                return;
            }

            $challenge = WebAuthn::generateChallenge();
            Session::set('webauthn_challenge', $challenge);
            Session::set('webauthn_challenge_time', time());
            Session::set('webauthn_username', $username);

            $options = WebAuthn::createAuthenticationOptions($credentials, $challenge);

            echo json_encode(['success' => true, 'options' => $options]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Complete authentication - verify and login
    public function authenticationComplete() {
        try {
            Session::start();

            $challenge = Session::get('webauthn_challenge');
            $challengeTime = Session::get('webauthn_challenge_time');
            $username = Session::get('webauthn_username');

            // Verify challenge is valid and not expired (5 minutes)
            if (!$challenge || !$challengeTime || (time() - $challengeTime) > 300) {
                http_response_code(400);
                echo json_encode(['error' => 'Challenge expired or invalid']);
                return;
            }

            if (!$username) {
                http_response_code(400);
                echo json_encode(['error' => 'Username not found in session']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['credential'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid request']);
                return;
            }

            $credential = $input['credential'];
            $credentialId = $credential['id'] ?? '';

            if (empty($credentialId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing credential ID']);
                return;
            }

            $user = $this->userModel->findByUsername($username);
            if (!$user) {
                http_response_code(404);
                echo json_encode(['error' => 'User not found']);
                return;
            }

            $storedCredential = $this->webauthnModel->findByCredentialId($credentialId);
            if (!$storedCredential || $storedCredential['user_id'] != $user['id']) {
                http_response_code(401);
                echo json_encode(['error' => 'Invalid credential']);
                return;
            }

            // Verify authentication response
            $response = $credential['response'] ?? [];
            $isValid = WebAuthn::verifyAuthenticationResponse(
                $credentialId,
                $response,
                $storedCredential,
                $challenge
            );

            if (!$isValid) {
                http_response_code(401);
                echo json_encode(['error' => 'Authentication failed']);
                return;
            }

            // Update counter and last used
            $counter = isset($response['authenticatorData']) ? ($storedCredential['counter'] + 1) : $storedCredential['counter'];
            $this->webauthnModel->updateCounter($credentialId, $counter, date('Y-m-d H:i:s'));

            // Log login
            if (class_exists('LoginLog')) {
                $loginLogModel = new LoginLog();
                $ipAddress = $this->getIpAddress();
                $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500);
                $loginTime = date('Y-m-d H:i:s');

                Session::start();
                $sessionToken = session_id();
                Auth::login($user['id'], $user);
                
                $loginLogModel->create([
                    'user_id' => $user['id'],
                    'session_token' => $sessionToken,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'login_at' => $loginTime,
                    'status' => 'success'
                ]);
            } else {
                Auth::login($user['id'], $user);
            }

            // Clear challenge
            Session::remove('webauthn_challenge');
            Session::remove('webauthn_challenge_time');
            Session::remove('webauthn_username');

            echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => '/dashboard']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // List user credentials
    public function listCredentials() {
        try {
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }

            $user = Auth::user();
            $credentials = $this->webauthnModel->findByUserId($user['id']);

            $result = array_map(function($cred) {
                return [
                    'id' => $cred['id'],
                    'credential_id' => $cred['credential_id'],
                    'created_at' => $cred['created_at'],
                    'last_used_at' => $cred['last_used_at']
                ];
            }, $credentials);

            echo json_encode(['success' => true, 'credentials' => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    // Delete credential
    public function deleteCredential() {
        try {
            if (!Auth::check()) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $credentialId = $input['credential_id'] ?? '';

            if (empty($credentialId)) {
                http_response_code(400);
                echo json_encode(['error' => 'Credential ID required']);
                return;
            }

            $user = Auth::user();
            $credential = $this->webauthnModel->findByCredentialId($credentialId);

            if (!$credential || $credential['user_id'] != $user['id']) {
                http_response_code(404);
                echo json_encode(['error' => 'Credential not found']);
                return;
            }

            $this->webauthnModel->delete($credentialId);
            echo json_encode(['success' => true, 'message' => 'Credential deleted']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    private function getIpAddress() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
}

