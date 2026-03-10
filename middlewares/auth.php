<?php

require_once __DIR__ . '/../src/Helpers/env.php'; // Asegúrate de que esta ruta sea correcta

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getAuthUser(): ?array {
    $authHeader = null;
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    // 1. Intentar por getallheaders (estándar)
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
    } 
    // 2. Intentar por variable de servidor (XAMPP/Apache fallback)
    elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } 
    // 3. Intentar por variable redireccionada (XAMPP fallback 2)
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }

    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        return null;
    }

    $token = str_replace('Bearer ', '', $authHeader);
    
    // OPCIONAL: Limpieza extra por si el cliente envía comillas accidentales
    $token = trim($token, '"'); 

    $secret = env('JWT_SECRET', 'clave_predeterminada_segura');

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return [
            'id'     => $decoded->sub ?? null,
            'correo' => $decoded->correo ?? null,
            'rol'    => $decoded->rol ?? null,
            'exp'    => $decoded->exp ?? null
        ];
    } catch (Exception $e) {
        return null;
    }
}
