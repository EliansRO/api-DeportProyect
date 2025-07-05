<?php
namespace App\Helpers;

class Response {
    public static function json($data, int $code = 200){
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}
