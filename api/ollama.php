<?php
// Pengaturan Koneksi
define('DB_HOST', 'localhost');
define('DB_NAME', 'rag_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Pengaturan Ollama
define('OLLAMA_BASE_URL', 'http://localhost:11434/api');
define('CHAT_MODEL', 'llama3');
define('EMBED_MODEL', 'nomic-embed-text');

// Fungsi Koneksi PDO Global
function getDB() {
    try {
        $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Koneksi Gagal: " . $e->getMessage());
    }
}