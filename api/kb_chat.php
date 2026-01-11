<?php
require_once '../config/config.php';

$input = json_decode(file_get_contents("php://input"), true);
$question = $input['question'] ?? '';

$knowledge = file_get_contents('../knowledge/text/dprd.txt');

// Prompt khusus Knowledge Base
$prompt = "
Kamu adalah asisten informasi.
Jawablah pertanyaan HANYA berdasarkan dokumen berikut.
Jika jawabannya tidak ada, katakan: 'Informasi tidak ditemukan dalam dokumen.'

DOKUMEN:
$knowledge

PERTANYAAN:
$question
";

$data = [
    "model" => OLLAMA_MODEL,
    "prompt" => $prompt,
    "stream" => false
];

$ch = curl_init(OLLAMA_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($data)
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
