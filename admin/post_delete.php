<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
checkAuth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id) {
    // Pegar imagem para apagar do servidor (opcional, mas bom)
    $stmt = $pdo->prepare("SELECT image_path FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();

    if ($post && !empty($post['image_path'])) {
        $file = __DIR__ . '/../' . $post['image_path'];
        if (file_exists($file)) {
            unlink($file);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit;
?>