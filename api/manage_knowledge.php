<?php
/**
 * SYUJA ENGINE - Knowledge & Session Management API
 * Upgrade: Support for File Management and Conversational Memory Reset
 */

// Memulai sesi agar bisa mengelola memori chat
session_start();

header('Content-Type: application/json');
require_once '../config/config.php';

$action = $_GET['action'] ?? 'list';

try {
    // 1. ACTION: LIST - Mengambil daftar file unik
    if ($action === 'list') {
        $stmt = $pdo->query("SELECT source_file, COUNT(*) as total_chunks, MAX(id) as last_id 
                             FROM knowledge_base 
                             GROUP BY source_file 
                             ORDER BY last_id DESC");
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $files]);
    } 

    // 2. ACTION: DELETE - Menghapus file tertentu dari database
    elseif ($action === 'delete') {
        $input = json_decode(file_get_contents("php://input"), true);
        $fileName = $input['file_name'] ?? '';

        if (empty($fileName)) {
            throw new Exception("Nama file tidak valid.");
        }

        $stmt = $pdo->prepare("DELETE FROM knowledge_base WHERE source_file = ?");
        $stmt->execute([$fileName]);

        echo json_encode([
            "status" => "success", 
            "message" => "Pengetahuan dari dokumen '$fileName' telah dihapus."
        ]);
    }

    // 3. ACTION: CLEAR_MEMORY - Menghapus riwayat percakapan (Conversational Memory)
    elseif ($action === 'clear_memory') {
        // Kosongkan array riwayat chat di session
        $_SESSION['chat_history'] = [];
        
        echo json_encode([
            "status" => "success", 
            "message" => "Memori percakapan berhasil dikosongkan. AI telah di-reset."
        ]);
    }

    // 4. ACTION: TRUNCATE - Hapus seluruh database (Opsional)
    elseif ($action === 'truncate') {
        $pdo->exec("TRUNCATE TABLE knowledge_base");
        $_SESSION['chat_history'] = []; // Ikut hapus memori
        
        echo json_encode([
            "status" => "success", 
            "message" => "Seluruh Knowledge Base dan memori telah dikosongkan."
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}