<?php
class WebAuthn {
    // Generate random challenge
    public static function generateChallenge($length = 32) {
        return base64_encode(random_bytes($length));
    }

    // Base64 URL encode (WebAuthn format)
    public static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    // Base64 URL decode (WebAuthn format)
    public static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    // Generate credential ID
    public static function generateCredentialId() {
        return self::base64UrlEncode(random_bytes(32));
    }

    // Create public key credential creation options
    public static function createRegistrationOptions($username, $userId, $challenge, $rpId = null) {
        if (!$rpId) {
            $rpId = $_SERVER['HTTP_HOST'] ?? 'localhost';
            // Remove port if present
            $rpId = preg_replace('/:\d+$/', '', $rpId);
        }

        $config = require __DIR__ . '/../config/app.php';
        $appName = $config['app_name'] ?? 'Indoprima Online';

        return [
            'challenge' => self::base64UrlEncode($challenge),
            'rp' => [
                'name' => $appName,
                'id' => $rpId
            ],
            'user' => [
                'id' => self::base64UrlEncode($userId),
                'name' => $username,
                'displayName' => $username
            ],
            'pubKeyCredParams' => [
                ['type' => 'public-key', 'alg' => -7], // ES256
                ['type' => 'public-key', 'alg' => -257] // RS256
            ],
            'authenticatorSelection' => [
                'authenticatorAttachment' => 'platform', // Platform authenticator (fingerprint, face ID)
                'userVerification' => 'preferred',
                'requireResidentKey' => false
            ],
            'timeout' => 60000,
            'attestation' => 'none'
        ];
    }

    // Create public key credential request options
    public static function createAuthenticationOptions($credentials, $challenge) {
        $allowCredentials = [];
        
        foreach ($credentials as $cred) {
            $allowCredentials[] = [
                'id' => $cred['credential_id'],
                'type' => 'public-key'
            ];
        }

        return [
            'challenge' => self::base64UrlEncode($challenge),
            'allowCredentials' => $allowCredentials,
            'userVerification' => 'preferred',
            'timeout' => 60000
        ];
    }

    // Verify credential response (simplified - production should use proper cryptographic verification)
    public static function verifyAuthenticationResponse($credentialId, $response, $storedCredential, $challenge, $origin = null) {
        // In production, you should verify:
        // 1. The signature using the stored public key
        // 2. The challenge matches
        // 3. The origin is correct
        // 4. The counter has increased
        // For now, we'll do basic validation
        
        if (!$storedCredential) {
            return false;
        }

        if ($storedCredential['credential_id'] !== $credentialId) {
            return false;
        }

        // Decode response
        $clientDataJSON = json_decode(self::base64UrlDecode($response['clientDataJSON'] ?? ''), true);
        if (!$clientDataJSON) {
            return false;
        }

        // Verify challenge
        $challengeDecoded = self::base64UrlEncode($challenge);
        if (!isset($clientDataJSON['challenge']) || $clientDataJSON['challenge'] !== $challengeDecoded) {
            return false;
        }

        // Verify type
        if (!isset($clientDataJSON['type']) || $clientDataJSON['type'] !== 'webauthn.get') {
            return false;
        }

        // Verify origin (optional, but recommended)
        if ($origin) {
            $expectedOrigin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
            if (!isset($clientDataJSON['origin']) || $clientDataJSON['origin'] !== $origin) {
                return false;
            }
        }

        return true;
    }
}

