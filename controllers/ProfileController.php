<?php
class ProfileController extends Controller {
    public function index() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $data = ['user' => $user];
        $this->view('profile/index', $data);
    }
    
    public function update() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $userModel = new User();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [];
            
            // Only update fields that are provided
            if (isset($_POST['username']) && !empty($_POST['username'])) {
                $data['username'] = $_POST['username'];
                
                // Check username uniqueness (except current user)
                $existingUser = $userModel->findByUsername($data['username']);
                if ($existingUser && $existingUser['id'] != $user['id']) {
                    Message::error('Username sudah digunakan');
                    $this->redirect('/profile');
                }
            }
            
            if (isset($_POST['namalengkap']) && !empty($_POST['namalengkap'])) {
                $data['namalengkap'] = $_POST['namalengkap'];
            }
            
            if (isset($_POST['email']) && !empty($_POST['email'])) {
                $data['email'] = $_POST['email'];
                
                // Check email uniqueness (except current user)
                $existingEmail = $userModel->findByEmail($data['email']);
                if ($existingEmail && $existingEmail['id'] != $user['id']) {
                    Message::error('Email sudah digunakan');
                    $this->redirect('/profile');
                }
            }
            
            // Handle picture upload
            if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Delete old picture
                    if ($user['picture']) {
                        $oldPicturePath = __DIR__ . '/../uploads/' . $user['picture'];
                        if (file_exists($oldPicturePath)) {
                            unlink($oldPicturePath);
                        }
                    }
                    $data['picture'] = $this->uploadPicture($_FILES['picture']);
                } catch (Exception $e) {
                    Message::error($e->getMessage());
                    $this->redirect('/profile');
                }
            }
            
            // Only update if there's data to update
            if (!empty($data)) {
                $userModel->update($user['id'], $data);
                Message::success('Profile berhasil diupdate');
            }
            
            $this->redirect('/profile');
        }
        
        $data = ['user' => $user];
        $this->view('profile/index', $data);
    }
    
    public function changePassword() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $userModel = new User();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                Message::error('Semua field harus diisi');
                $this->redirect('/profile/change-password');
            }
            
            if (!$userModel->verifyPassword($currentPassword, $user['password'])) {
                Message::error('Password lama salah');
                $this->redirect('/profile/change-password');
            }
            
            if ($newPassword !== $confirmPassword) {
                Message::error('Password baru dan konfirmasi tidak cocok');
                $this->redirect('/profile/change-password');
            }
            
            if (strlen($newPassword) < 6) {
                Message::error('Password minimal 6 karakter');
                $this->redirect('/profile/change-password');
            }
            
            $userModel->update($user['id'], ['password' => $newPassword]);
            Message::success('Password berhasil diubah');
            $this->redirect('/profile/change-password');
        }
        
        $data = ['user' => $user];
        $this->view('profile/change-password', $data);
    }
    
    public function settings() {
        Auth::requireAuth();
        
        $user = Auth::user();
        $data = ['user' => $user];
        $this->view('profile/setting', $data);
    }
    
    private function uploadPicture($file) {
        $config = require __DIR__ . '/../config/app.php';
        $uploadPath = $config['upload_path'];
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extension, $config['allowed_image_types'])) {
            throw new Exception('Format file tidak diizinkan');
        }
        
        if ($file['size'] > $config['max_file_size']) {
            throw new Exception('Ukuran file terlalu besar');
        }
        
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $targetPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        throw new Exception('Gagal mengupload file');
    }
}

