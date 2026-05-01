<?php

namespace App\Core;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth
{
    private $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public static function generateToken($userId, $email, $role)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $payload = [
            'iss' => $config['app']['url'],
            'iat' => time(),
            'exp' => time() + $config['jwt']['expiration'],
            'userId' => $userId,
            'email' => $email,
            'role' => $role,
        ];

        return JWT::encode($payload, $config['jwt']['secret'], 'HS256');
    }

    public static function verifyToken($token)
    {
        try {
            $config = require __DIR__ . '/../../config/config.php';
            $decoded = JWT::decode(
                $token,
                new Key($config['jwt']['secret'], 'HS256')
            );

            return (array) $decoded;
        } catch (\Exception $e) {
            throw new \Exception("Invalid token: " . $e->getMessage());
        }
    }

    public static function getToken()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public static function authenticate()
    {
        $token = self::getToken();
        if (!$token) {
            Response::unauthorized('Token not provided');
        }

        try {
            return self::verifyToken($token);
        } catch (\Exception $e) {
            Response::unauthorized($e->getMessage());
        }
    }

    public static function isAdmin($user)
    {
        return isset($user['role']) && $user['role'] === 'admin';
    }

    public static function belongsToRealEstate($userId, $realEstateId)
    {
        // Esta lógica se puede ampliar según necesites
        return true;
    }
}
