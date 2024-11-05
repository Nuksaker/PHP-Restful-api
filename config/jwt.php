<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JWTHandler
{
    private $jwt_secret;
    private $token_expiration;

    public function __construct()
    {
        $this->jwt_secret = $_ENV['JWT_SECRET_KEY'];
        $this->token_expiration = $_ENV['JWT_TOKEN_EXPIRATION'] ?? 3600; // Default 1 hour if not set
    }

    public function generateToken($user_id)
    {
        $issuedAt = time();
        $expire = $issuedAt + $this->token_expiration;

        $payload = [
            'iss' => 'Resfulapi',
            'aud' => 'adasoft',
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $user_id
        ];

        return JWT::encode($payload, $this->jwt_secret, 'HS256');
    }

    public function validateToken($token)
    {
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_secret, 'HS256'));
            return $decoded; // Returns decoded payload if token is valid
        } catch (ExpiredException $e) {
            return 'expired';
        } catch (Exception $e) {
            error_log("JWT Validation Error: " . $e->getMessage());
            return false;
        }
    }
}

