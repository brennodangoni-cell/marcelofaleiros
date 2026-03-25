<?php
// Simple live preview skeleton matching the blog's dark theme
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #7ed957;
            --color-dark: #050505;
            --color-darker: #000000;
            --color-card: #111111;
            --color-border: #222222;
            --color-light-gray: #a1a1aa;
            --color-white: #ffffff;
            --font-primary: 'Poppins', 'Segoe UI', sans-serif;
            --font-heading: 'Playfair Display', Georgia, serif;
            --radius-full: 9999px;
        }

        body {
            margin: 0;
            font-family: var(--font-primary);
            background: var(--color-darker);
            color: var(--color-light-gray);
            line-height: 1.8;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .header {
            background: rgba(0, 0, 0, 0.85);
            padding: 15px 0;
            border-bottom: 1px solid var(--color-border);
            text-align: center;
        }

        .header h3 {
            margin: 0;
            color: #fff;
            font-family: var(--font-heading);
            font-size: 20px;
        }

        .header h3 span {
            color: var(--color-primary);
        }

        .article-container {
            max-width: 860px;
            margin: 40px auto;
            padding: 40px 50px;
            background: var(--color-card);
            border: 1px solid var(--color-border);
            border-radius: 24px;
        }

        @media (max-width: 768px) {
            .article-container {
                margin: 20px auto;
                padding: 25px 20px;
                border-radius: 16px;
            }
        }

        .article-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .cat-tag {
            display: inline-block;
            padding: 8px 18px;
            border-radius: var(--radius-full);
            background: rgba(126, 217, 87, 0.1);
            color: var(--color-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .article-title {
            font-family: var(--font-heading);
            font-size: 38px;
            color: var(--color-white);
            line-height: 1.2;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .article-cover {
            width: 100%;
            border-radius: 16px;
            margin-bottom: 40px;
            display: none;
            border: 1px solid var(--color-border);
        }

        .article-content {
            font-size: 16px;
            color: #d4d4d8;
            line-height: 1.9;
        }

        .article-content h2,
        .article-content h3 {
            font-family: var(--font-heading);
            color: var(--color-white);
            margin-top: 40px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .article-content a {
            color: var(--color-primary);
            text-decoration: underline;
        }

        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            margin: 20px 0;
        }

        .article-content blockquote {
            border-left: 4px solid var(--color-primary);
            padding: 20px;
            margin: 30px 0;
            font-style: italic;
            color: #e4e4e7;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 0 12px 12px 0;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>Marcelo<span>Faleiros</span></h3>
    </div>
    <div class="article-container">
        <div class="article-header">
            <span class="cat-tag" id="preview-category">Sua Categoria</span>
            <h1 class="article-title" id="preview-title">Título da Postagem</h1>
        </div>
        <img id="preview-image" src="" class="article-cover" alt="Capa">
        <div class="article-content ql-editor" id="preview-content">
            <p>Comece a escrever para visualizar aqui...</p>
        </div>
    </div>

    <script>
        window.addEventListener('message', function (event) {
            const data = event.data;
            if (data.type === 'updatePreview') {
                document.getElementById('preview-title').innerText = data.title || 'Título da Postagem';
                document.getElementById('preview-category').innerText = data.category || 'Categoria';

                const contentDiv = document.getElementById('preview-content');
                contentDiv.innerHTML = data.content || '<p>Comece a escrever para visualizar aqui...</p>';

                const img = document.getElementById('preview-image');
                if (data.imageSrc) {
                    img.src = data.imageSrc;
                    img.style.display = 'block';
                } else if (!data.hasExistingImage) {
                    img.style.display = 'none';
                }
            }
        });
    </script>
</body>

</html>