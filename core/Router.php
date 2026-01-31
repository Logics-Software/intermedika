<?php
class Router {
    private $routes = [];
    private $params = [];
    
    public function add($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }
    
    public function get($path, $controller, $action) {
        $this->add('GET', $path, $controller, $action);
    }
    
    public function post($path, $controller, $action) {
        $this->add('POST', $path, $controller, $action);
    }
    
    public function put($path, $controller, $action) {
        $this->add('PUT', $path, $controller, $action);
    }

    public function patch($path, $controller, $action) {
        $this->add('PATCH', $path, $controller, $action);
    }
    
    public function delete($path, $controller, $action) {
        $this->add('DELETE', $path, $controller, $action);
    }
    
    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        
        // Router dispatch
        
        // Skip routing for static files (assets, uploads, images, etc.)
        $staticExtensions = ['.css', '.js', '.jpg', '.jpeg', '.png', '.gif', '.svg', '.ico', '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.doc', '.docx', '.xls', '.xlsx'];
        $staticPaths = ['/assets/', '/uploads/', '/favicon.ico'];
        
        foreach ($staticPaths as $staticPath) {
            if (strpos($uri, $staticPath) === 0) {
                // Let web server handle static files
                return;
            }
        }
        
        // Check for static file extensions only if URI ends with known extension
        // Don't treat URLs with dots in path segments as static files
        foreach ($staticExtensions as $ext) {
            if (substr($uri, -strlen($ext)) === $ext) {
                // Only treat as static file if it's actually a file path, not a route parameter
                // Route parameters with dots (like .260100175) should not be treated as static files
                $pathBeforeExt = substr($uri, 0, -strlen($ext));
                // If the part before extension contains slashes (like /penjualan/view/), it's likely a route
                if (strpos($pathBeforeExt, '/') !== false) {
                    // This is likely a route parameter, not a static file
                    break;
                }
                // Let web server handle static files
                return;
            }
        }
        
        // Handle method override for PUT/PATCH/DELETE (browsers don't support these natively)
        // Store raw input for later use (php://input can only be read once)
        $rawInput = null;
        if ($method === 'POST') {
            // Read raw input first (before it's consumed)
            $rawInput = file_get_contents('php://input');
            
            // Check form data first
            if (isset($_POST['_method'])) {
                $method = strtoupper($_POST['_method']);
                // Store raw input for form-urlencoded data too (for manual parsing)
                $GLOBALS['_RAW_INPUT'] = $rawInput;
            }
            // Also check JSON body for method override
            elseif (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
                $jsonInput = json_decode($rawInput, true);
                if (isset($jsonInput['_method'])) {
                    $method = strtoupper($jsonInput['_method']);
                }
                // Store raw input for controllers to use
                $GLOBALS['_RAW_INPUT'] = $rawInput;
            }
            // For form-urlencoded without _method, also store raw input for manual parsing
            elseif (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/x-www-form-urlencoded') !== false) {
                $GLOBALS['_RAW_INPUT'] = $rawInput;
            }
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE'])) {
            // Store raw input for PUT/PATCH/DELETE requests
            $rawInput = file_get_contents('php://input');
            $GLOBALS['_RAW_INPUT'] = $rawInput;
        }
        
        // Normalize URI - remove trailing slash except for root
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }
        
        // Handle root route
        if ($uri === '/' && $method === 'GET') {
            if (Auth::check()) {
                header('Location: /dashboard');
            } else {
                header('Location: /login');
            }
            exit;
        }
        
        foreach ($this->routes as $route) {
            $pattern = $this->convertToRegex($route['path']);
            
            // Normal route matching
            
            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                $this->params = $matches;
                
                $controllerName = $route['controller'];
                $actionName = $route['action'];
                
                if (!class_exists($controllerName)) {
                    error_log("Router error: Controller class '{$controllerName}' not found.");
                    http_response_code(500);
                    echo "500 - Internal Server Error";
                    return;
                }
                
                $controller = new $controllerName();
                
                if (!method_exists($controller, $actionName)) {
                    error_log("Router error: Method '{$actionName}' not found in controller '{$controllerName}'.");
                    http_response_code(500);
                    echo "500 - Internal Server Error";
                    return;
                }
                
                call_user_func_array([$controller, $actionName], $this->params);
                return;
            }
        }
        
        // 404 Not Found (no debug output)
        http_response_code(404);
        echo "404 - Page Not Found";
        return;
    }
    
    private function convertToRegex($path) {
        // Very simple approach: replace {param} before escaping
        // Replace {param} placeholders with capture group
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^\/]+)', $path);
        // Escape forward slashes only
        $pattern = str_replace('/', '\/', $pattern);
        return '#^' . $pattern . '$#';
    }
}

