<?php
/**
 * SYUJA ENGINE - Advanced PDF Processor (Windows/Laragon Optimized)
 * Upgrade: Fix untuk Dashboard (source_file) & Validasi Ekstraksi
 */

header('Content-Type: application/json');
require_once '../config/config.php';

// Menambah batas waktu eksekusi agar tidak timeout saat proses embedding banyak chunk
set_time_limit(600); 

try {
    // Validasi file yang diunggah
    if (!isset($_FILES['pdf_file']) || $_FILES['pdf_file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("File tidak ditemukan atau terjadi kesalahan saat upload.");
    }

    $fileTmpPath = $_FILES['pdf_file']['tmp_name'];
    $fileName = $_FILES['pdf_file']['name']; // Nama file yang akan muncul di dashboard
    
    // Lokasi sementara hasil ekstraksi pdftotext
    $outputTxt = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '.txt';

    /**
     * TAHAP 1: Ekstraksi Teks dengan pdftotext
     */
    $cmd = "pdftotext -layout -enc UTF-8 " . escapeshellarg($fileTmpPath) . " " . escapeshellarg($outputTxt);
    exec($cmd, $output, $returnVar);

    // Fallback khusus Windows/Laragon jika pemanggilan langsung gagal
    if ($returnVar !== 0) {
        $cmd = "cmd /c pdftotext -layout -enc UTF-8 " . escapeshellarg($fileTmpPath) . " " . escapeshellarg($outputTxt);
        exec($cmd, $output, $returnVar);
    }

    if ($returnVar !== 0 || !file_exists($outputTxt)) {
        throw new Exception("Sistem gagal mengekstrak teks. Pastikan pdftotext tersedia di Laragon.");
    }

    $text = file_get_contents($outputTxt);

    /**
     * TAHAP 2: Pembersihan Teks
     */
    // Menghapus spasi ganda agar chunking lebih efisien
    $text = preg_replace('/\s+/', ' ', $text);
    $text = trim($text);

    if (empty($text) || strlen($text) < 10) {
        throw new Exception("PDF tidak berisi teks (mungkin hasil scan/gambar).");
    }

    /**
     * TAHAP 3: Smart Overlapping Chunking
     */
    $maxChunkSize = 1000;
    $overlap = 150; 
    $chunks = [];
    
    $textLen = strlen($text);
    for ($i = 0; $i < $textLen; $i += ($maxChunkSize - $overlap)) {
        $chunk = substr($text, $i, $maxChunkSize);
        if (strlen(trim($chunk)) > 20) {
            $chunks[] = $chunk;
        }
        if ($i + $maxChunkSize >= $textLen) break;
    }

    if (empty($chunks)) {
        throw new Exception("Gagal membagi teks menjadi bagian-bagian (chunking).");
    }

    /**
     * TAHAP 4: Embedding & Simpan ke Database
     */
    $pdo->beginTransaction();
    $successCount = 0;

    foreach ($chunks as $content) {
        $ch = curl_init(OLLAMA_EMBED_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode([
                "model" => EMBED_MODEL, 
                "prompt" => $content
            ]),
            CURLOPT_TIMEOUT => 45 
        ]);

        $res = curl_exec($ch);
        $resData = json_decode($res, true);
        curl_close($ch);

        // Pastikan kolom source_file sudah ada di tabel knowledge_base Anda
        if (isset($resData['embedding'])) {
            $stmt = $pdo->prepare("INSERT INTO knowledge_base (content, embedding, source_file) VALUES (?, ?, ?)");
            $stmt->execute([
                $content, 
                json_encode($resData['embedding']), 
                $fileName // Disimpan agar muncul di daftar "Database Documents"
            ]);
            $successCount++;
        }
    }

    $pdo->commit();
    if (file_exists($outputTxt)) unlink($outputTxt);

    echo json_encode([
        "status" => "success", 
        "message" => "Berhasil! $successCount potongan informasi dari '$fileName' telah dipelajari."
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode([
        "status" => "error", 
        "message" => "Proses Gagal: " . $e->getMessage()
    ]);
}