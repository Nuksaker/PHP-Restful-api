<?php
require_once 'config/jwt.php';

class AuthController
{
    private $jwtHandler;

    public function __construct()
    {
        $this->jwtHandler = new JWTHandler();
    }

    public function authenticate()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $jwt_secret_key = $data['JWT_SECRET_KEY'] ?? null;
        $key = $data['KEY'] ?? null;

        if ($jwt_secret_key && $key) {
            // Validate the keys
            if ($this->validateKeys($jwt_secret_key, $key)) {
                putenv("JWT_SECRET_KEY=$jwt_secret_key");
                $token = $this->jwtHandler->generateToken(1);
                header('Authorization: Bearer ' . $token);
                return json_encode(['token' => $token]);
            }
        }

        http_response_code(401);
        return json_encode(['status' => 'error', 'message' => 'Invalid credentials']);
    }

    private function validateKeys($jwt_secret_key, $key)
    {
        // ตรวจสอบว่า JWT_SECRET_KEY ตรงกับที่กำหนดไว้ใน .env
        if ($jwt_secret_key !== $_ENV['JWT_SECRET_KEY']) {
            return false;
        }

        // ตรวจสอบ KEY (ในตัวอย่างนี้ เราจะตรวจสอบว่า KEY เป็น "tpro")
        if ($key !== "tpro") {
            return false;
        }

        return true;
    }
}
