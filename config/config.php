<?php
define('OLLAMA_CHAT_URL', 'http://localhost:11434/api/generate');
define('OLLAMA_EMBED_URL', 'http://localhost:11434/api/embeddings');

define('CHAT_MODEL', 'llama3');
define('EMBED_MODEL', 'nomic-embed-text');

$pdo = new PDO("mysql:host=localhost;dbname=rag_db", "root", "");
