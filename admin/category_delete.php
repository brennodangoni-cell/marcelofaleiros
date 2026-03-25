<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    // Primeiro atualizar as postagens desta categoria para NULL
    $stmt = $pdo->prepare("UPDATE posts SET category_id = NULL WHERE category_id = ?");
    $stmt->execute([$id]);

    // Depois excluir a categoria
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: categories.php');
exit;
?>