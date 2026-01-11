<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input['prompt'] ?? '';

$data = [
    "model" => OLLAMA_MODEL,
    "prompt" => $prompt,
    "stream" => false
];

$ch = curl_init(OLLAMA_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

echo $response;
