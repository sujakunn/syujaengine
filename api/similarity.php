<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once 'similarity.php';

$input = json_decode(file_get_contents("php://input"), true);
$question = $input['question'] ?? '';

if (empty($question)) {
    echo json_encode(['error' => 'Pertanyaan kosong']);
    exit;
}

// 1. Dapatkan Embedding Pertanyaan
$payload = ["model" => EMBED_MODEL, "prompt" => $question];
$ch = curl_init(OLLAMA_BASE_URL . "/embeddings");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$res = curl_exec($ch);
$qEmbedding = json_decode($res, true)['embedding'];

// 2. Cari Konteks Terdekat di Database
$pdo = getDB();
$stmt = $pdo->query("SELECT content, embedding FROM knowledge_base");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$matches = [];
foreach ($data as $row) {
    $score = cosineSimilarity($qEmbedding, json_decode($row['embedding'], true));
    $matches[] = ['content' => $row['content'], 'score' => $score];
}

// Urutkan berdasarkan skor tertinggi
usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);
$context = $matches[0]['content'] ?? 'Tidak ada data.';

// 3. Kirim ke Ollama dengan Prompt yang Lebih Baik
$prompt = "Anda adalah asisten cerdas. Gunakan konteks berikut untuk menjawab pertanyaan.\n\n"
        . "KONTEKS:\n$context\n\n"
        . "PERTANYAAN:\n$question\n\n"
        . "JAWABAN:";

$chatPayload = ["model" => CHAT_MODEL, "prompt" => $prompt, "stream" => false];
$ch = curl_init(OLLAMA_BASE_URL . "/generate");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chatPayload));
echo curl_exec($ch);