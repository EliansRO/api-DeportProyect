<?php

require_once __DIR__ . '/../src/Helpers/env.php'; // Asegúrate de que esta ruta sea correcta

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function getAuthUser(): ?array {
    // Obtener encabezados de autorización
    $headers = function_exists('getallheaders') ? getallheaders() : [];

    if (!isset($headers['Authorization'])) {
        return null;
    }

    $authHeader = $headers['Authorization'];

    if (!str_starts_with($authHeader, 'Bearer ')) {
        return null;
    }

    $token = str_replace('Bearer ', '', $authHeader);
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
        // Puedes hacer log del error aquí si lo necesitas
        return null;
    }
}
