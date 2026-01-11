<?php
/**
 * SYUJA ENGINE - Advanced RAG Core API
 * Feature: Conversational Memory & Smart Context Retrieval
 */

session_start(); // Memulai sesi untuk menyimpan memori chat
header('Content-Type: application/json');
require_once '../config/config.php';
require_once 'similarity.php';

// Tingkatkan batas waktu eksekusi untuk proses embedding dan similarity
set_time_limit(300);

// 1. Validasi Input
$input = json_decode(file_get_contents("php://input"), true);
$question = trim($input['question'] ?? '');

if (empty($question)) {
    echo json_encode(["response" => "Pertanyaan tidak boleh kosong.", "status" => "error"]);
    exit;
}

try {
    // 2. Load Conversational Memory (Riwayat Chat)
    // Mengambil maksimal 5 pertukaran terakhir agar prompt tidak terlalu gemuk
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    
    $historyContext = "";
    if (!empty($_SESSION['chat_history'])) {
        foreach ($_SESSION['chat_history'] as $chat) {
            $historyContext .= "User: " . $chat['q'] . "\nAI: " . $chat['a'] . "\n";
        }
    }

    // 3. Generate Embedding untuk Pertanyaan Terbaru
    $payload = [
        "model" => EMBED_MODEL,
        "prompt" => $question
    ];

    $ch = curl_init(OLLAMA_EMBED_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 || !$res) {
        throw new Exception("Ollama Server (Embed) tidak merespon.");
    }

    $resData = json_decode($res, true);
    $qEmbedding = $resData['embedding'] ?? null;
    
    if (!$qEmbedding) throw new Exception("Gagal mendapatkan vektor embedding.");

    // 4. Retrieval: Cari Konteks Paling Relevan di Database
    $stmt = $pdo->prepare("SELECT content, embedding, source_file FROM knowledge_base");
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $bestScore = 0;
    $bestContent = "";
    $sourceFile = "";
    $threshold = 0.45; // Batas minimal relevansi

    foreach ($data as $row) {
        $score = cosineSimilarity($qEmbedding, json_decode($row['embedding'], true));
        if ($score > $bestScore) {
            $bestScore = $score;
            $bestContent = $row['content'];
            $sourceFile = $row['source_file'];
        }
    }

    // 5. Konstruksi Advanced Prompt (Knowledge + Memory)
    if ($bestScore < $threshold) {
        // Jika tidak relevan dengan dokumen, biarkan AI merespon berdasarkan memori atau menolak halus
        $prompt = "Anda adalah SyujaEngine. Riwayat chat sebelumnya:\n$historyContext\n"
                . "Pertanyaan: $question\n"
                . "Instruksi: Jika pertanyaan tidak berkaitan dengan dokumen yang Anda pelajari, jawablah bahwa Anda tidak menemukan informasi tersebut di database dokumen Anda.";
    } else {
        $prompt = "Anda adalah SyujaEngine, asisten ahli yang jujur. Gunakan KONTEKS DOKUMEN dan RIWAYAT CHAT untuk menjawab.\n\n"
                . "RIWAYAT CHAT:\n$historyContext\n"
                . "KONTEKS DOKUMEN:\n$bestContent\n\n"
                . "PERTANYAAN: $question\n\n"
                . "JAWABAN:";
    }

    // 6. Generate Jawaban dari Ollama
    $chatPayload = [
        "model" => CHAT_MODEL,
        "prompt" => $prompt,
        "stream" => false,
        "options" => ["temperature" => 0.4]
    ];

    $ch = curl_init(OLLAMA_CHAT_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($chatPayload)
    ]);

    $chatRes = curl_exec($ch);
    $chatCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($chatCode !== 200 || !$chatRes) {
        throw new Exception("Ollama Server (Chat) tidak merespon.");
    }

    $finalData = json_decode($chatRes, true);
    $aiResponse = $finalData['response'] ?? "AI gagal merespon.";

    // 7. Simpan ke Memori Sesi (Maksimal 5)
    $_SESSION['chat_history'][] = ["q" => $question, "a" => $aiResponse];
    if (count($_SESSION['chat_history']) > 5) {
        array_shift($_SESSION['chat_history']);
    }

    // 8. Kirim Respon Terstruktur
    echo json_encode([
        "response" => $aiResponse,
        "score" => round($bestScore, 4),
        "source" => $sourceFile, // Menyertakan nama file sumber
        "status" => "success"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "response" => "Terjadi kesalahan: " . $e->getMessage(),
        "status" => "error"
    ]);
}