<?php
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$post = null;
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch categories for the list
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = $_POST['content'] ?? '';
    $status = $_POST['status'] ?? 'published';
    $category_id = empty($_POST['category_id']) ? null : (int) $_POST['category_id'];
    $new_category = trim($_POST['new_category'] ?? '');
    $image_path = $post['image_path'] ?? null;

    // Logic: if new_category is not empty, use it or find existing ID
    if (!empty($new_category)) {
        $stmtCatCheck = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmtCatCheck->execute([$new_category]);
        $existing = $stmtCatCheck->fetch();
        if ($existing) {
            $category_id = $existing['id'];
        } else {
            $stmtInsCat = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
            $stmtInsCat->execute([$new_category, createSlug($new_category)]);
            $category_id = $pdo->lastInsertId();
        }
    }

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (in_array($fileType, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
            $newName = uniqid() . '.webp';
            $uploadPath = __DIR__ . '/../uploads/' . $newName;
            $dbImagePath = 'uploads/' . $newName;

            if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                unlink(__DIR__ . '/../' . $image_path);
            }

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
                imagewebp($image, $uploadPath, 80);
                imagedestroy($image);
                $image_path = $dbImagePath;
            } else {
                $fallbackPath = __DIR__ . '/../uploads/' . uniqid() . '.' . $fileType;
                move_uploaded_file($tmpName, $fallbackPath);
                $image_path = 'uploads/' . basename($fallbackPath);
            }
        } else {
            $error = 'Formato de imagem não suportado.';
        }
    }

    if (empty($title)) {
        $error = 'O título é obrigatório.';
    } else if (empty($error)) {
        $slug = createSlug($title);
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

require_once __DIR__ . '/header.php';
?>

<style>
    /* Premium Visual Editor Layout */
    .editor-grid {
        display: grid;
        grid-template-columns: 1fr 400px;
        gap: 30px;
        align-items: start;
    }

    @media (min-width: 1300px) {
        .editor-grid {
            grid-template-columns: 1fr 550px;
        }
    }

    @media (max-width: 1000px) {
        .editor-grid {
            grid-template-columns: 1fr;
        }
    }

    .preview-container {
        position: sticky;
        top: 20px;
        background: #fff;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .preview-toolbar {
        padding: 12px 20px;
        border-bottom: 1px solid var(--border-light);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #fdfdfd;
    }

    .preview-actions {
        display: flex;
        gap: 5px;
    }

    .preview-btn {
        padding: 6px 12px;
        border: 1px solid var(--border-light);
        background: #fff;
        border-radius: 6px;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted);
        transition: all 0.2s;
    }

    .preview-btn.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }

    #preview-iframe {
        width: 100%;
        height: calc(100vh - 120px);
        border: none;
        transition: width 0.3s ease;
        margin: 0 auto;
    }

    .mobile-view #preview-iframe {
        width: 375px;
        border-left: 1px solid var(--border-light);
        border-right: 1px solid var(--border-light);
    }

    /* Constrain massive photos in editor */
    .ql-editor img {
        max-height: 350px !important;
        object-fit: contain;
        width: auto !important;
    }

    .category-row {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 15px;
    }
</style>

<div class="editor-grid">
    <div class="card">
        <h2><?= $id ? 'Editar' : 'Nova' ?> Postagem</h2>
        
        <?php if ($error): ?>
                <div style="background: var(--danger-light); color: var(--danger); padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                    <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data" id="postForm">
            <div class="form-group">
                <label for="title">Título da Postagem</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($post['title'] ?? '') ?>" placeholder="Ex: Melhores formas de lidar com a ansiedade" required autocomplete="off">
            </div>

            <div class="category-row">
                <div class="form-group">
                    <label for="category_id">Categoria Existente</label>
                    <select id="category_id" name="category_id" class="form-control">
                        <option value="">Nenhuma / Sem categoria</option>
                        <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($post['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                        <?php endforeach; ?>
                    </select>
                    <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Escolha uma categoria já criada acima.</p>
                </div>
                <div class="form-group">
                    <label for="new_category" style="color: var(--primary);">Ou Criar Nova</label>
                    <input type="text" id="new_category" name="new_category" placeholder="Nome da nova categoria..." class="form-control" autocomplete="off" style="border-color: var(--primary-light);">
                    <p style="font-size: 11px; color: var(--primary); margin-top: 5px; font-weight: 600;">⚠️ Se preenchido, este campo ignora a seleção ao lado.</p>
                </div>
            </div>

            <div class="form-group">
                <label for="status">Status da Publicação</label>
                <select id="status" name="status" class="form-control">
                    <option value="published" <?= ($post['status'] ?? 'published') == 'published' ? 'selected' : '' ?>>Publicado (Visível para todos)</option>
                    <option value="draft" <?= ($post['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Rascunho (Salvo porém oculto)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="excerpt">Resumo / Chamada (Aparece nos cards e no Google)</label>
                <textarea id="excerpt" name="excerpt" class="form-control" rows="3" placeholder="Escreva um breve resumo cativante..."><?= htmlspecialchars($post['excerpt'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="image">Imagem de Capa (Header)</label>
                <?php if (!empty($post['image_path'])): ?>
                        <div style="margin-bottom: 15px; background: #f9fafb; padding: 10px; border-radius: 8px; border: 1px solid var(--border-light); display: inline-block;">
                            <p style="font-size: 12px; margin-bottom: 8px; color: var(--text-muted);">Imagem atual:</p>
                            <img src="../<?= htmlspecialchars($post['image_path']) ?>" alt="Capa" style="height: 80px; width: auto; border-radius: 6px; display: block; box-shadow: var(--shadow-sm);">
                        </div>
                <?php endif; ?>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <p style="font-size: 11px; color: var(--text-muted); margin-top: 5px;">Recomendado: 1200x600px. O sistema otimiza para WebP automaticamente.</p>
            </div>

            <div class="form-group">
                <label>Conteúdo Principal</label>
                <input type="hidden" name="content" id="hiddenContent">
                <div id="editor"><?= $post['content'] ?? '' ?></div>
            </div>

            <div style="display: flex; gap: 15px; align-items: center; margin-top: 30px;">
                <button type="submit" class="btn" id="btnSubmit">
                    <i class="fas fa-save"></i> <span id="btnText">Salvar Alterações</span>
                </button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
                <span id="loadingMsg" style="display:none; color: var(--primary); font-size: 14px;"><i class="fas fa-spinner fa-spin"></i> Processando...</span>
            </div>
        </form>
    </div>

    <!-- Live Preview System -->
    <div class="preview-container" id="preview-wrapper">
        <div class="preview-toolbar">
            <span style="font-size: 14px; font-weight: 700; color: var(--text-dark);"><i class="fas fa-eye"></i> Visualização Realtime</span>
            <div class="preview-actions">
                <button class="preview-btn active" id="view-desktop" title="Modo Desktop"><i class="fas fa-desktop"></i></button>
                <button class="preview-btn" id="view-mobile" title="Modo Mobile"><i class="fas fa-mobile-alt"></i></button>
            </div>
        </div>
        <iframe id="preview-iframe" src="preview.php"></iframe>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Toolbar customizada Quill
    var toolbarOptions = [
        [{ 'header': [1, 2, 3, false] }],
        ['bold', 'italic', 'underline', 'strike'],
        ['blockquote', 'code-block'],
        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
        [{ 'align': [] }],
        ['link', 'image', 'video'],
        ['clean']
    ];

    var quill = new Quill('#editor', {
        theme: 'snow',
        modules: { toolbar: toolbarOptions }
    });

    const titleInput = document.getElementById('title');
    const catSelect = document.getElementById('category_id');
    const newCatInput = document.getElementById('new_category');
    const iframe = document.getElementById('preview-iframe');
    const previewWrapper = document.getElementById('preview-wrapper');
    const hasExistingImage = <?= !empty($post['image_path']) ? 'true' : 'false' ?>;
    const existingImageSrc = "<?= !empty($post['image_path']) ? '../' . $post['image_path'] : '' ?>";

    function updatePreview() {
        const title = titleInput.value;
        const category = newCatInput.value || (catSelect.options[catSelect.selectedIndex] ? catSelect.options[catSelect.selectedIndex].text : '');
        const content = quill.root.innerHTML;

        iframe.contentWindow.postMessage({
            type: 'updatePreview',
            title: title,
            category: category,
            content: content,
            hasExistingImage: hasExistingImage,
            imageSrc: existingImageSrc
        }, '*');
    }

    // Eventos para atualização
    titleInput.addEventListener('input', updatePreview);
    catSelect.addEventListener('change', updatePreview);
    newCatInput.addEventListener('input', updatePreview);
    quill.on('text-change', function() {
        updatePreview();
    });

    // Toggle Desktop / Mobile
    document.getElementById('view-desktop').addEventListener('click', function() {
        previewWrapper.classList.remove('mobile-view');
        this.classList.add('active');
        document.getElementById('view-mobile').classList.remove('active');
    });

    document.getElementById('view-mobile').addEventListener('click', function() {
        previewWrapper.classList.add('mobile-view');
        this.classList.add('active');
        document.getElementById('view-desktop').classList.remove('active');
    });

    // Enviar dados iniciais quando iframe carregar
    iframe.onload = updatePreview;

    // Monitorar upload de imagem local para preview (opcional)
    document.getElementById('image').addEventListener('change', function(e) {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                iframe.contentWindow.postMessage({
                    type: 'updatePreview',
                    imageSrc: event.target.result,
                    title: titleInput.value,
                    category: newCatInput.value || catSelect.options[catSelect.selectedIndex].text,
                    content: quill.root.innerHTML
                }, '*');
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Submit handler
    var form = document.getElementById('postForm');
    var btnSubmit = document.getElementById('btnSubmit');
    var btnText = document.getElementById('btnText');
    var loadingMsg = document.getElementById('loadingMsg');

    form.onsubmit = function() {
        var content = document.querySelector('input[name=content]');
        content.value = quill.root.innerHTML;
        btnSubmit.disabled = true;
        btnText.innerText = 'Salvando...';
        loadingMsg.style.display = 'inline-block';
    };
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
