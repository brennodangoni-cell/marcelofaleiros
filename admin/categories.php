<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

$stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin:0;">Categorias</h2>
        <a href="category_form.php" class="btn"><i class="fas fa-plus"></i> Nova Categoria</a>
    </div>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Slug</th>
                <th width="150">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td data-label="ID"><?= $cat['id'] ?></td>
                <td data-label="Nome"><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                <td data-label="Slug" style="color: var(--text-light);"><?= htmlspecialchars($cat['slug']) ?></td>
                <td data-label="Ações">
                    <a href="category_form.php?id=<?= $cat['id'] ?>" class="btn btn-small btn-secondary"><i class="fas fa-edit"></i></a>
                    <a href="category_delete.php?id=<?= $cat['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta categoria? Isso não removerá as postagens, mas elas ficarão sem categoria.')"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($categories) === 0): ?>
            <tr>
                <td colspan="4" style="text-align: center; padding: 30px;">Nenhuma categoria encontrada.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>