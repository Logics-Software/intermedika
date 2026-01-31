<?php
class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../config/app.php';
            session_name($config['session_name']);
            session_start();
        }
    }
    
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
    
    public static function has($key) {
        return isset($_SESSION[$key]);
    }
    
    public static function remove($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    
    public static function destroy() {
        session_destroy();
    }
    
    public static function flash($key, $value = null) {
        if ($value === null) {
            $message = $_SESSION['flash'][$key] ?? null;
            if (isset($_SESSION['flash'][$key])) {
                unset($_SESSION['flash'][$key]);
            }
            return $message;
        }
        $_SESSION['flash'][$key] = $value;
    }
}

