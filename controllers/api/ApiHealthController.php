<?php
class ApiHealthController extends Controller {
    /**
     * Health check / Ping endpoint
     * Returns API status and server information
     */
    public function index() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Allow other methods for testing purposes or strict GET
        // if ($method !== 'GET') {
        //     $this->json(['success' => false, 'message' => 'Method not allowed'], 405);
        //     return;
        // }
        
        try {
            // Test database connection
            $db = Database::getInstance();
            $db->getConnection();
            $dbStatus = 'connected';
            $dbError = null;
        } catch (Exception $e) {
            $dbStatus = 'disconnected';
            $dbError = $e->getMessage();
        }

        // Get headers (polyfill for getallheaders)
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }
        }
        
        // Build URL
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $url = $protocol . '://' . $host . $requestUri;
        
        $response = [
            'success' => true,
            'message' => 'API is running',
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s'),
            'server' => [
                'php_version' => PHP_VERSION,
                'server_time' => date('Y-m-d H:i:s'),
                'timezone' => date_default_timezone_get()
            ],
            'database' => [
                'status' => $dbStatus,
                'error' => $dbError
            ],
            // Debugging/Testing fields
            'args' => $_GET,
            'headers' => $headers,
            'url' => $url
        ];
        
        // If database is disconnected, return 503 Service Unavailable
        if ($dbStatus === 'disconnected') {
            $response['success'] = false;
            $response['status'] = 'error';
            $response['message'] = 'Database connection failed';
            $this->json($response, 503);
        } else {
            $this->json($response, 200);
        }
    }
}

