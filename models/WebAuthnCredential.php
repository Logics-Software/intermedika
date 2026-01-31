<?php
class WebAuthnCredential {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create($data) {
        $sql = "INSERT INTO webauthn_credentials (user_id, credential_id, public_key, counter, aaguid, last_used_at) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            $data['user_id'],
            $data['credential_id'],
            $data['public_key'],
            $data['counter'] ?? 0,
            $data['aaguid'] ?? null,
            $data['last_used_at'] ?? null
        ];
        
        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function findByCredentialId($credentialId) {
        $sql = "SELECT * FROM webauthn_credentials WHERE credential_id = ?";
        return $this->db->fetchOne($sql, [$credentialId]);
    }

    public function findByUserId($userId) {
        $sql = "SELECT * FROM webauthn_credentials WHERE user_id = ? ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function updateCounter($credentialId, $counter, $lastUsedAt = null) {
        $sql = "UPDATE webauthn_credentials SET counter = ?, last_used_at = ? WHERE credential_id = ?";
        $lastUsed = $lastUsedAt ?? date('Y-m-d H:i:s');
        $this->db->query($sql, [$counter, $lastUsed, $credentialId]);
        return true;
    }

    public function delete($credentialId) {
        $sql = "DELETE FROM webauthn_credentials WHERE credential_id = ?";
        $this->db->query($sql, [$credentialId]);
        return true;
    }

    public function deleteByUserId($userId) {
        $sql = "DELETE FROM webauthn_credentials WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return true;
    }
}

