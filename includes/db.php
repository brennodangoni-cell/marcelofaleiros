<?php
require_once __DIR__ . '/config.php';

try {
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE
    );

    CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE,
        excerpt TEXT,
        content TEXT,
        image_path TEXT,
        category_id INTEGER,
        status TEXT DEFAULT 'published',
        views INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories (id)
    );
    ";
    
    $pdo->exec($query);

    // Try to add new columns to existing table if it was already created before
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN excerpt TEXT");
    } catch (PDOException $e) { /* column exists */ }
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN status TEXT DEFAULT 'published'");
    } catch (PDOException $e) { /* column exists */ }
    try {
        $pdo->exec("ALTER TABLE posts ADD COLUMN views INTEGER DEFAULT 0");
    } catch (PDOException $e) { /* column exists */ }

} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

function createSlug($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
?>