<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$post = null;
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch categories
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $category_id = empty($_POST['category_id']) ? null : (int)$_POST['category_id'];
    $image_path = $post['image_path'] ?? null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $newName = uniqid() . '.webp';
            $uploadPath = __DIR__ . '/../uploads/' . $newName;
            $dbImagePath = 'uploads/' . $newName;

            // Delete old image if exists
            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }

            // Convert to webp
            $image = null;
            if ($fileType === 'jpg' || $fileType === 'jpeg') {
                $image = @imagecreatefromjpeg($tmpName);
            } elseif ($fileType === 'png') {
                $image = @imagecreatefrompng($tmpName);
                if ($image !== false) {
                    imagepalettetotruecolor($image);
                    imagealphablending($image, true);
                    imagesavealpha($image, true);
                }
            } elseif ($fileType === 'webp') {
                $image = @imagecreatefromwebp($tmpName);
            } elseif ($fileType === 'gif') {
                $image = @imagecreatefromgif($tmpName);
            }

            if ($image !== false) {
                // Resize if too large (e.g. width > 1200)
                $width = imagesx($image);
                $height = imagesy($image);
                if ($width > 1200) {
                    $newWidth = 1200;
                    $newHeight = floor($height * ($newWidth / $width));
                    $resized = imagecreatetruecolor($newWidth, $newHeight);
                    
                    if ($fileType === 'png' || $fileType === 'webp') {
                        imagealphablending($resized, false);
                        imagesavealpha($resized, true);
                        $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                        imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
                    }
                    
                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($image);
                    $image = $resized;
                }

                imagewebp($image, $uploadPath, 80); // 80 quality
                imagedestroy($image);
                $image_path = $dbImagePath;
            } else {
                // Fallback to move_uploaded_file if conversion fails
                $fallbackPath = __DIR__ . '/../uploads/' . uniqid() . '.' . $fileType;
                move_uploaded_file($tmpName, $fallbackPath);
                $image_path = 'uploads/' . basename($fallbackPath);
            }
        } else {
            $error = 'Formato de imagem não suportado. Use JPG, PNG ou WEBP.';
        }
    }

    if (empty($title)) {
        $error = 'O título é obrigatório.';
    } else if (empty($error)) {
        $slug = createSlug($title);
        // Ensure slug is unique
        $slugCheck = $pdo->prepare("SELECT id FROM posts WHERE slug = ? AND id != ?");
        $slugCheck->execute([$slug, $id ?? 0]);
        if ($slugCheck->fetch()) {
            $slug .= '-' . uniqid();
        }

        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE posts SET title = ?, slug = ?, excerpt = ?, content = ?, category_id = ?, image_path = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$title, $slug, $excerpt, $content, $category_id, $image_path, $status, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, excerpt, content, category_id, image_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $excerpt, $content, $category_id, $image_path, $status]);
            }
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao salvar postagem: " . $e->getMessage();
        }
    }
}
?>

<div class="card">
    <h2><?= $id ? 'Editar' : 'Nova' ?> Postagem</h2>
    
    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" id="postForm">
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($post['title'] ?? '') ?>" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="published" <?= ($post['status'] ?? 'published') == 'published' ? 'selected' : '' ?>>Publicado (Visível no site)</option>
                    <option value="draft" <?= ($post['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Rascunho (Apenas no admin)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="category_id">Categoria</label>
                <div style="display: flex; gap: 10px;">
                    <select id="category_id" name="category_id" class="form-control" style="flex: 1;">
                        <option value="">Sem categoria</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="flex: 1;">
                        <input type="text" name="new_category" placeholder="Ou crie nova..." class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="excerpt">Resumo (Aparece no card do blog e melhora o SEO no Google)</label>
            <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="Digite uma breve chamada para este artigo..."><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="image">Imagem de Capa (será otimizada automaticamente)</label>
            <?php if (!empty($post['image_path'])): ?>
                <div style="margin-bottom: 10px;">
                    <img src="../<?= htmlspecialchars($post['image_path']) ?>" alt="Capa" style="max-width: 200px; border-radius: 4px;">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form-control" accept="image/*">
        </div>

        <div class="form-group">
            <label>Conteúdo</label>
            <input type="hidden" name="content" id="hiddenContent">
            <div id="editor"><?= $post['content'] ?? '' ?></div>
        </div>

        <div style="display: flex; gap: 15px; align-items: center; margin-top: 30px;">
            <button type="submit" class="btn" id="btnSubmit">
                <i class="fas fa-save"></i> <span id="btnText">Salvar Postagem</span>
            </button>
            <a href="index.php" class="btn btn-secondary">Cancelar</a>
            <span id="loadingMsg" style="display:none; color: var(--primary); font-size: 14px;"><i class="fas fa-spinner fa-spin"></i> Salvando e otimizando imagens, aguarde...</span>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    var form = document.getElementById('postForm');
    var btnSubmit = document.getElementById('btnSubmit');
    var btnText = document.getElementById('btnText');
    var loadingMsg = document.getElementById('loadingMsg');

    form.onsubmit = function() {
        var content = document.querySelector('input[name=content]');
        content.value = quill.root.innerHTML;
        
        // Show loading state
        btnSubmit.disabled = true;
        btnSubmit.style.opacity = '0.7';
        btnText.innerText = 'Salvando...';
        loadingMsg.style.display = 'inline-block';
    };
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>