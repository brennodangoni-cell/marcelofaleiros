<?php
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
$category = null;
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');

    if (empty($name)) {
        $error = 'O nome da categoria é obrigatório.';
    } else {
        $slug = createSlug($name);

        try {
            if ($id) {
                // Update
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $id]);
            } else {
                // Insert
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
                $stmt->execute([$name, $slug]);
            }
            header('Location: categories.php');
            exit;
        } catch (PDOException $e) {
            $error = "Erro ao salvar categoria: " . $e->getMessage() . " (O slug já existe?)";
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2><?= $id ? 'Editar' : 'Nova' ?> Categoria</h2>

    <?php if ($error): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Nome da Categoria</label>
            <input type="text" id="name" name="name" class="form-control"
                value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
        </div>
        <div style="display: flex; gap: 15px; margin-top: 20px;">
            <button type="submit" class="btn"><i class="fas fa-save"></i> Salvar</button>
            <a href="categories.php" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>