<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
 $config = require __DIR__ . '/../../../config/config.php';
class FeedController 
{
    public function index() 
    {
        global $config;
        $username = null;
        $isLoggedIn = false;

        if (isset($_COOKIE['token'])) {
            try {
                $decoded = JWT::decode($_COOKIE['token'], new Key($config['JWT_SECRET'], $config['JWT_HEADER']));
                $username = $decoded->username ?? 'Usuario';
                $isLoggedIn = true;
            } catch (Exception $e) {
                // Debug: Muestra el error para identificar el problema
                error_log('JWT Decode Error: ' . $e->getMessage());
                // O temporalmente: echo 'Error: ' . $e->getMessage(); exit;
            }
        }

        require_once __DIR__ . '/../view/FeedView.php';
    }
}