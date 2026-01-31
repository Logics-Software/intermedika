<?php
class Auth {
    public static function check() {
        Session::start();
        return Session::has('user_id');
    }
    
    public static function user() {
        Session::start();
        if (self::check()) {
            $userId = Session::get('user_id');
            $userModel = new User();
            return $userModel->findById($userId);
        }
        return null;
    }
    
    public static function login($userId, $userData) {
        Session::start();
        Session::set('user_id', $userId);
        Session::set('user_role', $userData['role']);
        Session::set('user_username', $userData['username']);
    }
    
    public static function logout() {
        Session::start();
        Session::destroy();
    }
    
    public static function isAdmin() {
        return self::user() && self::user()['role'] === 'admin';
    }
    
    public static function isManajemen() {
        $user = self::user();
        return $user && ($user['role'] === 'admin' || $user['role'] === 'manajemen');
    }
    
    public static function isOperator() {
        $user = self::user();
        return $user && $user['role'] === 'operator';
    }

    public static function isSales() {
        $user = self::user();
        return $user && $user['role'] === 'sales';
    }
    
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: /login');
            exit;
        }
    }
    
    public static function requireRole($roles) {
        self::requireAuth();
        $user = self::user();
        if (!in_array($user['role'], (array)$roles)) {
            header('Location: /dashboard');
            exit;
        }
    }
}

