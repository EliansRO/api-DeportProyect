<?php
$data = file_get_contents('php://input');

file_put_contents(__DIR__ . '/test_output.txt', $data ?: 'VACÍO');
echo json_encode(['raw_input' => $data ?: 'VACÍO']);
