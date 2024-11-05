<?php

use App\Database;

date_default_timezone_set('Asia/Bangkok');
if (class_exists('Locale')) {
    Locale::setDefault('th_TH');
} else {
    setlocale(LC_ALL, 'th_TH.utf8');
}

require_once __DIR__ . '/autoload.php';
require_once 'vendor/autoload.php';
require_once 'config/database.php';
require_once 'controllers/AuthController.php';
require_once 'middleware/AuthMiddleware.php';
require_once 'helpers/helpers.php';
require_once 'core/Route.php';



// Load environment variables from .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// เพิ่ม error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Now you can use the environment variables
$jwt_secret = $_ENV['JWT_SECRET_KEY'];

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$database = new Database();
$db = $database->getConnection();
$auth = new AuthController();
$authMiddleware = new AuthMiddleware();

// Load all module routes
$modules = ['product']; // Add more modules as needed
$routes = [];
foreach ($modules as $module) {
    $module_routes = require_once "modules/{$module}/routes/route.php";
    $routes = array_merge_recursive($routes, $module_routes);
}


$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$path = ltrim($request_uri, '/');
$path_parts = explode('/', $path);
$base_path = $path_parts[0];

// Authentication route for login
if ($path === 'auth' && $method === 'POST') {
    echo $auth->authenticate();
} elseif ($path === 'connectionDB' && $method === 'GET') {
    $database = new Database();
    $db = $database->getConnection();
    if ($db) {
        echo json_encode(["status" => "success", "message" => "Database connection successful"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    }
} else if ($authMiddleware->authenticate()) {
    $matched_route = null;
    $params = [];

    foreach ($routes[$method] as $route_path => $route_info) {
        $route_parts = explode('/', $route_path);
        if (count($route_parts) === count($path_parts)) {
            $match = true;
            for ($i = 0; $i < count($route_parts); $i++) {
                if (strpos($route_parts[$i], '{') === 0 && strpos($route_parts[$i], '}') === strlen($route_parts[$i]) - 1) {
                    $param_name = trim($route_parts[$i], '{}');
                    $params[$param_name] = $path_parts[$i];
                } elseif ($route_parts[$i] !== $path_parts[$i]) {
                    $match = false;
                    break;
                }
            }
            if ($match) {
                $matched_route = $route_info;
                break;
            }
        }
    }

    if ($matched_route) {
        $callback = $matched_route['callback'];
        $module = $matched_route['module'];
        list($controller, $action) = explode('@', $callback);

        $controllerClass = "App\\Modules\\" . ucfirst($module) . "\\Controllers\\" . $controller;

        if (class_exists($controllerClass)) {
            $controller_instance = new $controllerClass($db);
            if (!empty($params)) {
                echo call_user_func_array([$controller_instance, $action], $params);
            } else {
                echo $controller_instance->$action();
            }
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Controller not found"]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Route not found"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Route not found"]);
}
