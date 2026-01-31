<?php
class AuthController extends Controller {
    public function login() {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            $loginLogModel = new LoginLog();
            $ipAddress = $this->getIpAddress();
            $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'unknown', 0, 500);
            $loginTime = date('Y-m-d H:i:s');
            
            if (empty($username) || empty($password)) {
                $loginLogModel->create([
                    'user_id' => null,
                    'session_token' => null,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'login_at' => $loginTime,
                    'status' => 'failed'
                ]);
                Session::flash('error', 'Username dan password harus diisi');
                $this->redirect('/login');
            }
            
            $userModel = new User();
            $user = $userModel->findByUsername($username);
            
            if ($user && $userModel->verifyPassword($password, $user['password'])) {
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
                // Session::flash('success', 'Login berhasil'); // Hidden - tidak menampilkan pesan sukses login
                $this->redirect('/dashboard');
            } else {
                $loginLogModel->create([
                    'user_id' => $user['id'] ?? null,
                    'session_token' => null,
                    'ip_address' => $ipAddress,
                    'user_agent' => $userAgent,
                    'login_at' => $loginTime,
                    'status' => 'failed'
                ]);
                Session::flash('error', 'Username atau password salah');
                $this->redirect('/login');
            }
        }
        
        $this->view('auth/login');
    }
    
    public function logout() {
        Session::start();
        $sessionToken = session_id();
        $loginLogModel = new LoginLog();
        $loginLogModel->markLogout($sessionToken);
        Auth::logout();
        Session::flash('success', 'Logout berhasil');
        $this->redirect('/login');
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

