<?php
require_once '../config/config.php';

$pdf = "../knowledge/pdf/dprd.pdf";
$txt = "../knowledge/text/dprd.txt";

exec("pdftotext $pdf $txt");

$text = file_get_contents($txt);

// 1️⃣ Chunking
$chunks = str_split($text, 1000);

foreach ($chunks as $chunk) {

    // 2️⃣ Embedding
    $payload = [
        "model" => EMBED_MODEL,
        "prompt" => $chunk
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

    $embedding = json_decode($res, true)['embedding'];

    // 3️⃣ Simpan
    $stmt = $pdo->prepare("INSERT INTO knowledge_base (content, embedding) VALUES (?, ?)");
    $stmt->execute([$chunk, json_encode($embedding)]);
}

echo "PDF berhasil diproses ke RAG";
