<?php
require_once __DIR__ . '/../includes/db.php';

// Pegar categorias para o menu
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Paginação e busca
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$category_slug = $_GET['categoria'] ?? null;
$search = $_GET['s'] ?? '';

$where = [];
$params = [];

if ($category_slug) {
    $where[] = "categories.slug = ?";
    $params[] = $category_slug;
}
if ($search) {
    $where[] = "(posts.title LIKE ? OR posts.content LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$whereClause = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Count total
$countQuery = "
    SELECT COUNT(*) 
    FROM posts 
    LEFT JOIN categories ON posts.category_id = categories.id 
    $whereClause
";
$stmtCount = $pdo->prepare($countQuery);
$stmtCount->execute($params);
$total = $stmtCount->fetchColumn();
$totalPages = ceil($total / $limit);

// Fetch posts
$query = "
    SELECT posts.*, categories.name as category_name, categories.slug as category_slug
    FROM posts 
    LEFT JOIN categories ON posts.category_id = categories.id 
    $whereClause
    " . (empty($where) ? "WHERE posts.status = 'published'" : " AND posts.status = 'published'") . "
    ORDER BY posts.created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Marcelo Faleiros</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #7ed957;
            --color-dark: #000000;
            --color-dark-gray: #1a1a1a;
            --color-gray: #2d2d2d;
            --color-light-gray: #cccccc;
            --color-white: #ffffff;
            --gradient-bg: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #2d2d2d 100%);
            --font-primary: 'Poppins', sans-serif;
            --font-heading: 'Playfair Display', serif;
        }
        body {
            margin: 0;
            font-family: var(--font-primary);
            background: var(--gradient-bg);
            background-attachment: fixed;
            color: var(--color-light-gray);
            line-height: 1.6;
        }
        a { text-decoration: none; color: inherit; }
        .header {
            background: rgba(0,0,0,0.95);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(126, 217, 87, 0.2);
            backdrop-filter: blur(10px);
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-family: var(--font-heading); font-size: 24px; color: var(--color-white); font-weight: bold; }
        .logo span { color: var(--color-primary); }
        .nav-links { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; }
        .nav-links a { color: var(--color-white); transition: color 0.3s; }
        .nav-links a:hover, .nav-links a.active { color: var(--color-primary); }
        
        .blog-header {
            text-align: center;
            padding: 80px 0 60px;
        }
        .blog-title {
            font-family: var(--font-heading);
            font-size: 48px;
            color: var(--color-white);
            margin-bottom: 10px;
        }
        .blog-title span { color: var(--color-primary); }
        
        .categories-bar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }
        .cat-tag {
            padding: 8px 16px;
            border-radius: 20px;
            background: rgba(126, 217, 87, 0.1);
            border: 1px solid rgba(126, 217, 87, 0.3);
            color: var(--color-white);
            font-size: 14px;
            transition: all 0.3s;
        }
        .cat-tag:hover, .cat-tag.active {
            background: var(--color-primary);
            color: var(--color-dark);
        }

        .search-bar {
            margin-bottom: 40px;
            padding: 0 20px;
        }
        .search-input {
            flex: 1;
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.1);
            background: var(--color-dark-gray);
            color: var(--color-white);
            font-family: var(--font-primary);
            font-size: 15px;
        }
        .search-input:focus {
            outline: none;
            border-color: var(--color-primary);
        }
        .search-btn {
            padding: 12px 25px;
            border-radius: 30px;
            border: none;
            background: var(--color-primary);
            color: var(--color-dark);
            cursor: pointer;
            transition: all 0.3s;
        }
        .search-btn:hover {
            background: #6bc245;
            transform: scale(1.05);
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 60px;
        }
        .post-card {
            background: var(--color-dark-gray);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(126, 217, 87, 0.1);
            border-color: rgba(126, 217, 87, 0.3);
        }
        .post-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .post-content { padding: 20px; }
        .post-cat {
            color: var(--color-primary);
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
        }
        .post-title {
            font-family: var(--font-heading);
            color: var(--color-white);
            font-size: 22px;
            margin: 0 0 10px 0;
            line-height: 1.3;
        }
        .post-excerpt {
            font-size: 14px;
            color: var(--color-light-gray);
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .post-date {
            font-size: 12px;
            color: #888;
        }

        .pagination { display: flex; justify-content: center; gap: 10px; margin-bottom: 60px; }
        .page-link {
            width: 40px; height: 40px;
            display: flex; justify-content: center; align-items: center;
            border-radius: 50%;
            background: var(--color-dark-gray);
            color: var(--color-white);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .page-link.active, .page-link:hover {
            background: var(--color-primary);
            color: var(--color-dark);
            border-color: var(--color-primary);
        }
        
        .footer { text-align: center; padding: 40px 0; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 40px; }

        @media (max-width: 768px) {
            .logo { font-size: 20px; }
            .nav-links { gap: 10px; font-size: 14px; }
            .blog-title { font-size: 36px; }
            .posts-grid { grid-template-columns: 1fr; }
            .search-bar { padding: 0; }
        }
    </style>
</head>
<body>

<header class="header">
    <div class="container nav">
        <a href="../index.html" class="logo">Marcelo<span>Faleiros</span></a>
        <ul class="nav-links">
            <li><a href="../index.html">Início</a></li>
            <li><a href="index.php" class="active">Blog</a></li>
        </ul>
    </div>
</header>

<div class="blog-header">
    <div class="container">
        <h1 class="blog-title">Blog <span>e Artigos</span></h1>
        <p>Conteúdos sobre terapia integrativa, bem-estar e desenvolvimento emocional.</p>
    </div>
</div>

<div class="container">
    <div class="categories-bar">
        <a href="index.php" class="cat-tag <?= !$category_slug ? 'active' : '' ?>">Todas</a>
        <?php foreach ($categories as $cat): ?>
            <a href="index.php?categoria=<?= $cat['slug'] ?>" class="cat-tag <?= $category_slug === $cat['slug'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Barra de Pesquisa -->
    <div class="search-bar">
        <form action="index.php" method="GET" style="display:flex; gap: 10px; max-width: 600px; margin: 0 auto;">
            <?php if($category_slug): ?>
                <input type="hidden" name="categoria" value="<?= htmlspecialchars($category_slug) ?>">
            <?php endif; ?>
            <input type="text" name="s" placeholder="Pesquisar artigos..." value="<?= htmlspecialchars($search) ?>" class="search-input">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <?php if ($search): ?>
        <p style="text-align: center; margin-bottom: 30px;">Resultados para: <strong><?= htmlspecialchars($search) ?></strong></p>
    <?php endif; ?>

    <div class="posts-grid">
        <?php foreach ($posts as $post): ?>
            <a href="post.php?slug=<?= $post['slug'] ?>" class="post-card">
                <?php if ($post['image_path']): ?>
                    <img src="../<?= htmlspecialchars($post['image_path']) ?>" class="post-img" alt="<?= htmlspecialchars($post['title']) ?>">
                <?php else: ?>
                    <div style="width:100%; height:200px; background:#222; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-image fa-3x" style="color:#444;"></i>
                    </div>
                <?php endif; ?>
                <div class="post-content">
                    <span class="post-cat"><?= htmlspecialchars($post['category_name'] ?? 'Artigo') ?></span>
                    <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                    <div class="post-excerpt">
                        <?php 
                        if (!empty($post['excerpt'])) {
                            echo htmlspecialchars($post['excerpt']);
                        } else {
                            $excerpt = strip_tags($post['content']);
                            echo mb_substr($excerpt, 0, 150) . '...';
                        }
                        ?>
                    </div>
                    <span class="post-date"><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (count($posts) === 0): ?>
        <p style="text-align:center; padding: 40px;">Nenhuma postagem encontrada.</p>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?p=<?= $i ?><?= $category_slug ? '&categoria='.$category_slug : '' ?>" class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Marcelo Faleiros. Todos os direitos reservados.</p>
</footer>

</body>
</html>