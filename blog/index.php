<?php
require_once __DIR__ . '/../includes/db.php';

// Pegar categorias para o menu
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

// Paginação e busca
$page = isset($_GET['p']) ? max(1, (int) $_GET['p']) : 1;
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
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #7ed957;
            --color-primary-hover: #8be066;
            --color-dark: #050505;
            --color-darker: #000000;
            --color-card: #111111;
            --color-card-hover: #151515;
            --color-border: #222222;
            --color-border-hover: #333333;
            --color-light-gray: #a1a1aa;
            --color-white: #ffffff;
            --gradient-bg: radial-gradient(circle at top right, #111111 0%, #000000 100%);
            --font-primary: 'Poppins', 'Segoe UI', sans-serif;
            --font-heading: 'Playfair Display', Georgia, serif;
            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-full: 9999px;
            --shadow-float: 0 20px 40px -10px rgba(0, 0, 0, 0.8);
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body {
            margin: 0;
            font-family: var(--font-primary);
            background: var(--color-darker);
            background-attachment: fixed;
            color: var(--color-light-gray);
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        .header {
            background: rgba(0, 0, 0, 0.85);
            padding: 20px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid var(--color-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .container {
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 24px;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: var(--font-heading);
            font-size: 26px;
            color: var(--color-white);
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .logo span {
            color: var(--color-primary);
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .nav-links a {
            color: var(--color-light-gray);
            transition: var(--transition-smooth);
            font-weight: 500;
            font-size: 15px;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--color-primary);
        }

        .blog-header {
            text-align: center;
            padding: 100px 0 70px;
            background: var(--gradient-bg);
            border-bottom: 1px solid var(--color-border);
        }

        .blog-title {
            font-family: var(--font-heading);
            font-size: 56px;
            color: var(--color-white);
            margin-bottom: 16px;
            font-weight: 700;
            letter-spacing: -1px;
            line-height: 1.1;
        }

        .blog-title span {
            background: linear-gradient(90deg, var(--color-primary), #4ade80);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .blog-header p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto;
            color: var(--color-light-gray);
        }

        .categories-bar {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
            margin: 40px 0 30px;
        }

        .cat-tag {
            padding: 8px 20px;
            border-radius: var(--radius-full);
            background: var(--color-card);
            border: 1px solid var(--color-border);
            color: var(--color-light-gray);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition-smooth);
        }

        .cat-tag:hover,
        .cat-tag.active {
            background: var(--color-white);
            color: var(--color-darker);
            border-color: var(--color-white);
            transform: translateY(-2px);
        }

        .search-bar {
            margin-bottom: 60px;
            padding: 0 20px;
        }

        .search-input {
            flex: 1;
            padding: 16px 24px;
            border-radius: var(--radius-full);
            border: 1px solid var(--color-border);
            background: var(--color-card);
            color: var(--color-white);
            font-family: var(--font-primary);
            font-size: 16px;
            transition: var(--transition-smooth);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--color-primary);
            background: var(--color-card-hover);
        }

        .search-input::placeholder {
            color: #555;
        }

        .search-btn {
            padding: 0 28px;
            border-radius: var(--radius-full);
            border: none;
            background: var(--color-primary);
            color: var(--color-darker);
            cursor: pointer;
            transition: var(--transition-smooth);
            font-size: 16px;
        }

        .search-btn:hover {
            background: var(--color-primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(126, 217, 87, 0.2);
        }

        .posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 40px;
            margin-bottom: 80px;
        }

        .post-card {
            background: var(--color-card);
            border-radius: var(--radius-lg);
            overflow: hidden;
            border: 1px solid var(--color-border);
            transition: var(--transition-smooth);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .post-card:hover {
            transform: translateY(-8px);
            background: var(--color-card-hover);
            border-color: var(--color-border-hover);
            box-shadow: var(--shadow-float);
        }

        .post-img-wrapper {
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--color-border);
        }

        .post-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            transition: transform 0.7s ease;
            display: block;
        }

        .post-card:hover .post-img {
            transform: scale(1.05);
        }

        .post-content {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .post-cat {
            color: var(--color-primary);
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 12px;
            display: inline-block;
        }

        .post-title {
            font-family: var(--font-heading);
            color: var(--color-white);
            font-size: 24px;
            margin: 0 0 16px 0;
            line-height: 1.3;
            font-weight: 700;
            transition: color 0.3s;
        }

        .post-card:hover .post-title {
            color: var(--color-primary);
        }

        .post-excerpt {
            font-size: 15px;
            color: var(--color-light-gray);
            margin-bottom: 24px;
            line-height: 1.7;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex-grow: 1;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--color-border);
            padding-top: 20px;
            margin-top: auto;
        }

        .post-date {
            font-size: 13px;
            color: #777;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .read-more {
            font-size: 13px;
            color: var(--color-white);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: var(--transition-smooth);
        }

        .post-card:hover .read-more {
            color: var(--color-primary);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-bottom: 80px;
        }

        .page-link {
            width: 44px;
            height: 44px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: var(--radius-full);
            background: var(--color-card);
            color: var(--color-white);
            border: 1px solid var(--color-border);
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .page-link.active,
        .page-link:hover {
            background: var(--color-white);
            color: var(--color-darker);
            border-color: var(--color-white);
            transform: translateY(-2px);
        }

        .footer {
            text-align: center;
            padding: 40px 0;
            border-top: 1px solid var(--color-border);
            background: var(--color-dark);
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .blog-header {
                padding: 60px 0 40px;
            }

            .blog-title {
                font-size: 40px;
            }

            .posts-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .post-content {
                padding: 24px;
            }

            .post-title {
                font-size: 22px;
            }
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
                <a href="index.php?categoria=<?= $cat['slug'] ?>"
                    class="cat-tag <?= $category_slug === $cat['slug'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($cat['name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Barra de Pesquisa -->
        <div class="search-bar">
            <form action="index.php" method="GET" style="display:flex; gap: 10px; max-width: 600px; margin: 0 auto;">
                <?php if ($category_slug): ?>
                    <input type="hidden" name="categoria" value="<?= htmlspecialchars($category_slug) ?>">
                <?php endif; ?>
                <input type="text" name="s" placeholder="Pesquisar artigos..." value="<?= htmlspecialchars($search) ?>"
                    class="search-input">
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <?php if ($search): ?>
            <p style="text-align: center; margin-bottom: 30px;">Resultados para:
                <strong><?= htmlspecialchars($search) ?></strong></p>
        <?php endif; ?>

        <div class="posts-grid">
            <?php foreach ($posts as $post): ?>
                <a href="post.php?slug=<?= $post['slug'] ?>" class="post-card">
                    <div class="post-img-wrapper">
                        <?php if ($post['image_path']): ?>
                            <img src="../<?= htmlspecialchars($post['image_path']) ?>" class="post-img"
                                alt="<?= htmlspecialchars($post['title']) ?>">
                        <?php else: ?>
                            <div
                                style="width:100%; height:240px; background:#18181b; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-image fa-3x" style="color:#3f3f46;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
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
                        <div class="post-footer">
                            <span class="post-date">
                                <i class="far fa-calendar-alt"></i>
                                <?= date('d/m/Y', strtotime($post['created_at'])) ?>
                            </span>
                            <span class="read-more">Ler mais <i class="fas fa-arrow-right"></i></span>
                        </div>
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
                    <a href="?p=<?= $i ?><?= $category_slug ? '&categoria=' . $category_slug : '' ?>"
                        class="page-link <?= $page === $i ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Marcelo Faleiros. Todos os direitos reservados.</p>
    </footer>

</body>

</html>