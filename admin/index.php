<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/header.php';

// Stats for dashboard
$postCount = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$catCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$viewsCount = $pdo->query("SELECT SUM(views) FROM posts")->fetchColumn() ?: 0;

$stmt = $pdo->query("
    SELECT posts.*, categories.name as category_name 
    FROM posts 
    LEFT JOIN categories ON posts.category_id = categories.id 
    ORDER BY posts.created_at DESC
");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="dashboard-cards">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-info">
            <h3><?= $postCount ?></h3>
            <p>Postagens</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
            <h3><?= $catCount ?></h3>
            <p>Categorias</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <h3><?= $viewsCount ?></h3>
            <p>Visualizações</p>
        </div>
    </div>
</div>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h2 style="margin:0;">Postagens</h2>
        <a href="post_form.php" class="btn"><i class="fas fa-plus"></i> Nova Postagem</a>
    </div>

    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th>Data</th>
                <th>Título</th>
                <th>Status</th>
                <th>Categoria</th>
                <th>Visitas</th>
                <th width="150">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($posts as $post): ?>
            <tr>
                <td data-label="Data"><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></td>
                <td data-label="Título">
                    <strong><?= htmlspecialchars($post['title']) ?></strong>
                </td>
                <td data-label="Status">
                    <?php if(($post['status'] ?? 'published') == 'draft'): ?>
                        <span style="background: #f1f5f9; color: #64748b; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Rascunho</span>
                    <?php else: ?>
                        <span style="background: rgba(126, 217, 87, 0.2); color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Publicado</span>
                    <?php endif; ?>
                </td>
                <td data-label="Categoria"><?= htmlspecialchars($post['category_name'] ?? 'Sem categoria') ?></td>
                <td data-label="Visitas"><?= $post['views'] ?? 0 ?></td>
                <td data-label="Ações">
                    <a href="post_form.php?id=<?= $post['id'] ?>" class="btn btn-small" style="background:#e2e8f0; color:#333;"><i class="fas fa-edit"></i></a>
                    <a href="post_delete.php?id=<?= $post['id'] ?>" class="btn btn-small btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta postagem?')"><i class="fas fa-trash"></i></a>
                    <?php if(($post['status'] ?? 'published') == 'published'): ?>
                        <a href="../blog/post.php?slug=<?= $post['slug'] ?>" target="_blank" class="btn btn-small" style="background:#f8fafc; border: 1px solid #ccc; color:#333;"><i class="fas fa-external-link-alt"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (count($posts) === 0): ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px;">Nenhuma postagem encontrada. Comece a criar seu blog!</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>