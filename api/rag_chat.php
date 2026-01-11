<?php
require_once '../config/config.php';
require_once 'similarity.php';

$input = json_decode(file_get_contents("php://input"), true);
$question = $input['question'] ?? '';

// Embedding pertanyaan
$payload = [
    "model" => EMBED_MODEL,
    "prompt" => $question
];

$ch = curl_init(OLLAMA_EMBED_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload)
]);

$res = curl_exec($ch);
curl_close($ch);

$qEmbedding = json_decode($res, true)['embedding'];

// Ambil semua knowledge
$data = $pdo->query("SELECT * FROM knowledge_base")->fetchAll();

$bestScore = 0;
$bestContent = "";

foreach ($data as $row) {
    $score = cosineSimilarity($qEmbedding, json_decode($row['embedding'], true));
    if ($score > $bestScore) {
        $bestScore = $score;
        $bestContent = $row['content'];
    }
}

// Prompt RAG
$prompt = "
Jawablah HANYA berdasarkan konteks berikut.
Jika tidak ada, jawab: 'Data tidak ditemukan.'

KONTEKS:
$bestContent

PERTANYAAN:
$question
";

$chatPayload = [
    "model" => CHAT_MODEL,
    "prompt" => $prompt,
    "stream" => false
];

$ch = curl_init(OLLAMA_CHAT_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($chatPayload)
]);

echo curl_exec($ch);
