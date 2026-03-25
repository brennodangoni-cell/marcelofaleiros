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
if ($readingTime < 1)
    $readingTime = 1;

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
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Highlight.js for code blocks from quill -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/monokai-sublime.min.css">
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
            --transition-smooth: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        body {
            margin: 0;
            font-family: var(--font-primary);
            background: var(--color-darker);
            background-attachment: fixed;
            color: var(--color-light-gray);
            line-height: 1.8;
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

        .article-container {
            max-width: 860px;
            margin: 80px auto;
            padding: 50px 60px;
            background: var(--color-card);
            border: 1px solid var(--color-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8);
        }

        @media (max-width: 768px) {
            .article-container {
                margin: 40px auto;
                padding: 30px 24px;
                border-radius: 16px;
            }
        }

        .article-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .cat-tag {
            display: inline-block;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            background: rgba(126, 217, 87, 0.1);
            color: var(--color-primary);
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .article-title {
            font-family: var(--font-heading);
            font-size: 48px;
            color: var(--color-white);
            line-height: 1.2;
            margin-bottom: 24px;
            font-weight: 700;
            letter-spacing: -1px;
        }

        .article-meta {
            color: #888;
            font-size: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            font-weight: 500;
        }

        .article-cover {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            border: 1px solid var(--color-border);
        }

        .article-content {
            font-size: 18px;
            color: #d4d4d8;
            line-height: 1.9;
        }

        .article-content h2,
        .article-content h3 {
            font-family: var(--font-heading);
            color: var(--color-white);
            margin-top: 50px;
            margin-bottom: 20px;
            font-weight: 700;
            line-height: 1.3;
        }

        .article-content h2 {
            font-size: 32px;
            letter-spacing: -0.5px;
        }

        .article-content h3 {
            font-size: 24px;
        }

        .article-content p {
            margin-bottom: 28px;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .article-content a {
            color: var(--color-primary);
            text-decoration: underline;
            text-underline-offset: 4px;
            transition: var(--transition-smooth);
        }

        .article-content a:hover {
            color: var(--color-white);
        }

        .article-content blockquote {
            border-left: 4px solid var(--color-primary);
            padding: 20px 24px;
            margin: 40px 0;
            font-style: italic;
            color: #e4e4e7;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 0 12px 12px 0;
            font-size: 20px;
        }

        /* Responsivo embed video (Quill usa iframe) */
        .ql-video {
            width: 100%;
            height: 450px;
            border: none;
            border-radius: 12px;
            margin: 30px 0;
        }

        .btn-back {
            display: inline-block;
            margin-top: 60px;
            padding: 14px 32px;
            border: 1px solid var(--color-primary);
            color: var(--color-primary);
            border-radius: var(--radius-full);
            transition: var(--transition-smooth);
            font-weight: 500;
            font-size: 15px;
        }

        .btn-back:hover {
            background: var(--color-primary);
            color: var(--color-darker);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(126, 217, 87, 0.2);
        }

        .share-buttons {
            margin-top: 80px;
            padding-top: 40px;
            border-top: 1px solid var(--color-border);
            text-align: center;
        }

        .share-buttons p {
            color: #a1a1aa;
            font-size: 16px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .share-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: var(--radius-full);
            margin: 0 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition-smooth);
            border: none;
        }

        .share-btn.whatsapp {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
        }

        .share-btn.whatsapp:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
        }

        .share-btn.copy-link {
            background: #27272a;
            color: white;
            border: 1px solid #3f3f46;
        }

        .share-btn.copy-link:hover {
            background: #3f3f46;
            transform: translateY(-2px);
        }

        .related-posts {
            margin-top: 80px;
            padding-top: 60px;
        }

        .related-posts h2 {
            font-family: var(--font-heading);
            color: var(--color-white);
            margin-bottom: 30px;
            font-size: 32px;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
        }

        .related-card {
            background: var(--color-card);
            border-radius: var(--radius-md);
            overflow: hidden;
            border: 1px solid var(--color-border);
            transition: var(--transition-smooth);
        }

        .related-card:hover {
            transform: translateY(-5px);
            border-color: var(--color-primary);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.5);
        }

        .related-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-bottom: 1px solid var(--color-border);
        }

        .related-card h4 {
            padding: 20px;
            margin: 0;
            color: var(--color-white);
            font-size: 15px;
            line-height: 1.5;
            font-weight: 600;
        }

        .footer {
            text-align: center;
            padding: 40px 0;
            border-top: 1px solid var(--color-border);
            margin-top: 80px;
            background: var(--color-dark);
            color: #666;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            .article-title {
                font-size: 36px;
            }

            .ql-video {
                height: 250px;
            }

            .share-btn {
                margin: 5px;
                width: calc(100% - 10px);
                justify-content: center;
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

    <div class="article-container">
        <div class="article-header">
            <?php if ($post['category_slug']): ?>
                <a href="index.php?categoria=<?= $post['category_slug'] ?>"
                    class="cat-tag"><?= htmlspecialchars($post['category_name']) ?></a>
            <?php endif; ?>

            <h1 class="article-title"><?= htmlspecialchars($post['title']) ?></h1>
            <div class="article-meta">
                <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($post['created_at'])) ?></span>
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
            <a href="https://wa.me/?text=<?= $whatsappMsg ?>" target="_blank" class="share-btn whatsapp"><i
                    class="fab fa-whatsapp"></i> WhatsApp</a>
            <button onclick="navigator.clipboard.writeText('<?= $currentUrl ?>'); alert('Link copiado!');"
                class="share-btn copy-link"><i class="fas fa-link"></i> Copiar Link</button>
        </div>

        <?php if (count($relatedPosts) > 0): ?>
            <div class="related-posts">
                <h2>Leia Também</h2>
                <div class="related-grid">
                    <?php foreach ($relatedPosts as $rel): ?>
                        <a href="post.php?slug=<?= $rel['slug'] ?>" class="related-card">
                            <?php if ($rel['image_path']): ?>
                                <img src="../<?= htmlspecialchars($rel['image_path']) ?>"
                                    alt="<?= htmlspecialchars($rel['title']) ?>">
                            <?php else: ?>
                                <div
                                    style="width:100%; height:140px; background:#18181b; display:flex; align-items:center; justify-content:center; border-bottom:1px solid var(--color-border);">
                                    <i class="fas fa-image fa-2x" style="color:#3f3f46;"></i>
                                </div>
                            <?php endif; ?>
                            <h4><?= htmlspecialchars($rel['title']) ?></h4>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Voltar para o Blog</a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Marcelo Faleiros. Todos os direitos reservados.</p>
    </footer>

</body>

</html>