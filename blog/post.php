<?php
require_once __DIR__ . '/../includes/db.php';

$slug = $_GET['slug'] ?? '';

if (!$slug) {
    header("Location: index.php");
    exit;
}

// Update views
$pdo->prepare("UPDATE posts SET views = views + 1 WHERE slug = ?")->execute([$slug]);

$stmt = $pdo->prepare("
    SELECT posts.*, categories.name as category_name, categories.slug as category_slug
    FROM posts 
    LEFT JOIN categories ON posts.category_id = categories.id 
    WHERE posts.slug = ? AND posts.status = 'published'
");
$stmt->execute([$slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header("Location: index.php");
    exit;
}

// Calculate reading time
$wordCount = str_word_count(strip_tags($post['content']));
$readingTime = ceil($wordCount / 200); // 200 words per minute
if ($readingTime < 1) $readingTime = 1;

// Fetch related posts
$relatedStmt = $pdo->prepare("
    SELECT slug, title, image_path, created_at 
    FROM posts 
    WHERE category_id = ? AND id != ? AND status = 'published'
    ORDER BY created_at DESC LIMIT 3
");
$relatedStmt->execute([$post['category_id'], $post['id']]);
$relatedPosts = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['title']) ?> - Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Highlight.js for code blocks from quill -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/monokai-sublime.min.css">
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
            line-height: 1.8;
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
        
        .article-container {
            max-width: 900px;
            margin: 60px auto;
            padding: 40px;
            background: rgba(26, 26, 26, 0.85);
            border: 1px solid rgba(126, 217, 87, 0.15);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            backdrop-filter: blur(10px);
        }
        @media (max-width: 768px) {
            .article-container {
                margin: 30px auto;
                padding: 20px;
                border-radius: 12px;
            }
        }
        .article-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .cat-tag {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            background: rgba(126, 217, 87, 0.1);
            color: var(--color-primary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .article-title {
            font-family: var(--font-heading);
            font-size: 42px;
            color: var(--color-white);
            line-height: 1.2;
            margin-bottom: 20px;
        }
        .article-meta {
            color: #888;
            font-size: 14px;
        }
        .article-cover {
            width: 100%;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .article-content {
            font-size: 18px;
            color: #ddd;
        }
        .article-content h2, .article-content h3 {
            font-family: var(--font-heading);
            color: var(--color-white);
            margin-top: 40px;
        }
        .article-content p {
            margin-bottom: 25px;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 20px 0;
        }
        .article-content a {
            color: var(--color-primary);
            text-decoration: underline;
        }
        .article-content blockquote {
            border-left: 4px solid var(--color-primary);
            padding-left: 20px;
            margin-left: 0;
            font-style: italic;
            color: #bbb;
        }
        /* Responsivo embed video (Quill usa iframe) */
        .ql-video {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 8px;
            margin: 20px 0;
        }
        .btn-back {
            display: inline-block;
            margin-top: 50px;
            padding: 12px 24px;
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
            border-radius: 30px;
            transition: all 0.3s;
        }
        .btn-back:hover {
            background: var(--color-primary);
            color: var(--color-dark);
        }

        .share-buttons {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .share-buttons p {
            color: #888;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .share-btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 30px;
            margin: 0 5px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        .share-btn.whatsapp { background: #25D366; color: white; }
        .share-btn.whatsapp:hover { background: #128C7E; }
        .share-btn.copy-link { background: #333; color: white; }
        .share-btn.copy-link:hover { background: #555; }

        .related-posts {
            margin-top: 60px;
        }
        .related-posts h2 {
            font-family: var(--font-heading);
            color: var(--color-white);
            margin-bottom: 20px;
        }
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .related-card {
            background: var(--color-dark-gray);
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.05);
            transition: transform 0.3s;
        }
        .related-card:hover { transform: translateY(-5px); border-color: var(--color-primary); }
        .related-card img { width: 100%; height: 120px; object-fit: cover; }
        .related-card h4 {
            padding: 15px;
            margin: 0;
            color: var(--color-white);
            font-size: 14px;
            line-height: 1.4;
        }

        .footer { text-align: center; padding: 40px 0; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 60px; }
        
        @media (max-width: 768px) {
            .article-title { font-size: 32px; }
            .ql-video { height: 250px; }
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

<div class="article-container">
    <div class="article-header">
        <?php if ($post['category_slug']): ?>
            <a href="index.php?categoria=<?= $post['category_slug'] ?>" class="cat-tag"><?= htmlspecialchars($post['category_name']) ?></a>
        <?php endif; ?>
        
        <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
        <div class="article-meta">
            <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
            <span style="margin: 0 10px;">&bull;</span>
            <span><i class="far fa-clock"></i> <?= $readingTime ?> min de leitura</span>
            <span style="margin: 0 10px;">&bull;</span>
            <span><i class="far fa-eye"></i> <?= $post['views'] ?> visualizações</span>
        </div>
    </div>

    <?php if ($post['image_path']): ?>
        <img src="../<?= htmlspecialchars($post['image_path']) ?>" class="article-cover" alt="Capa do artigo">
    <?php endif; ?>

    <div class="article-content ql-editor">
        <?= $post['content'] ?>
    </div>

    <!-- Share Buttons -->
    <div class="share-buttons">
        <p>Compartilhe este artigo:</p>
        <?php 
        $currentUrl = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $whatsappMsg = urlencode("Veja este artigo do Dr. Marcelo Faleiros: " . $post['title'] . " - " . $currentUrl);
        ?>
        <a href="https://wa.me/?text=<?= $whatsappMsg ?>" target="_blank" class="share-btn whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
        <button onclick="navigator.clipboard.writeText('<?= $currentUrl ?>'); alert('Link copiado!');" class="share-btn copy-link"><i class="fas fa-link"></i> Copiar Link</button>
    </div>

    <?php if (count($relatedPosts) > 0): ?>
    <div class="related-posts">
        <h2>Leia Também</h2>
        <div class="related-grid">
            <?php foreach ($relatedPosts as $rel): ?>
                <a href="post.php?slug=<?= $rel['slug'] ?>" class="related-card">
                    <?php if ($rel['image_path']): ?>
                        <img src="../<?= htmlspecialchars($rel['image_path']) ?>" alt="<?= htmlspecialchars($rel['title']) ?>">
                    <?php endif; ?>
                    <h4><?= htmlspecialchars($rel['title']) ?></h4>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="text-align: center;">
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para o Blog</a>
    </div>
</div>

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Marcelo Faleiros. Todos os direitos reservados.</p>
</footer>

</body>
</html>