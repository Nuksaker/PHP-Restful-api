<?php
require_once 'config/jwt.php';

class AuthMiddleware
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JWTHandler();
    }

    public function authenticate()
    {
        $headers = $this->getAllHeaders();
        $token = null;

        foreach ($headers as $key => $value) {
            if ($key === 'Authorization-Token') {
                $token = str_replace('Bearer ', '', $value);
                break;
            }
        }

        if ($token) {
            $decoded = $this->jwtHandler->validateToken($token);

            if (is_object($decoded)) {
                return true;
            } elseif ($decoded === 'expired') {
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Token has expired', 'code' => 'TOKEN_EXPIRED']);
                return false;
            } elseif ($decoded === false) {
                // Token is invalid
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Invalid token', 'code' => 'INVALID_TOKEN']);
                return false;
            }
        }

        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        return false;
    }

    private function getAllHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
