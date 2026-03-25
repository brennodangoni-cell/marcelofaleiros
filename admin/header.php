<?php
require_once __DIR__ . '/../includes/auth.php';
checkAuth();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Blog - Marcelo Faleiros</title>
    <!-- Quill CSS for rich text editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7ed957;
            --primary-hover: #6bc245;
            --dark: #000000;
            --darker: #0a0a0a;
            --card-bg: #1a1a1a;
            --border: rgba(126, 217, 87, 0.2);
            --border-light: #333333;
            --text: #cccccc;
            --text-light: #888888;
            --white: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--dark);
            color: var(--text);
            margin: 0;
            display: flex;
        }

        .sidebar {
            width: 260px;
            background-color: var(--card-bg);
            color: #fff;
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-light);
            z-index: 100;
        }

        .sidebar-header {
            padding: 30px 20px;
            background-color: var(--darker);
            text-align: center;
            border-bottom: 1px solid var(--border-light);
        }

        .sidebar-header h2 {
            margin: 0;
            color: #fff;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .sidebar-header span {
            color: var(--primary);
        }

        .nav-links {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 25px;
            color: var(--text);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 15px;
            font-weight: 500;
        }

        .nav-links a i {
            font-size: 18px;
            width: 20px;
            text-align: center;
        }

        .nav-links a:hover {
            background-color: rgba(126, 217, 87, 0.1);
            color: var(--primary);
            border-right: 4px solid var(--primary);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            border-top: 1px solid var(--border-light);
        }

        .logout-btn:hover {
            background-color: #ef4444;
            color: #fff;
        }

        .main-content {
            margin-left: 260px;
            padding: 40px;
            flex-grow: 1;
            min-height: 100vh;
            box-sizing: border-box;
            max-width: calc(100% - 260px);
        }

        h1, h2, h3 {
            color: var(--white);
            font-weight: 600;
            margin-top: 0;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: var(--card-bg);
            padding: 25px;
            border-radius: 12px;
            border: 1px solid var(--border-light);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--border);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            background: rgba(126, 217, 87, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        .stat-info h3 {
            margin: 0;
            font-size: 24px;
            color: var(--white);
        }
        .stat-info p {
            margin: 0;
            color: var(--text-light);
            font-size: 14px;
            font-weight: 500;
        }

        .card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            margin-bottom: 24px;
            border: 1px solid var(--border-light);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: var(--primary);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(126, 217, 87, 0.2);
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(126, 217, 87, 0.3);
        }

        .btn-danger {
            background-color: transparent;
            border: 1px solid #ef4444;
            color: #ef4444;
            box-shadow: none;
        }

        .btn-danger:hover {
            background-color: #ef4444;
            color: #fff;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            border: 1px solid var(--border-light);
            color: var(--text);
            box-shadow: none;
        }

        .btn-secondary:hover {
            background-color: var(--border-light);
            color: var(--white);
        }

        .btn-small {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 6px;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
        }

        th {
            background-color: var(--darker);
            color: var(--text-light);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 15px;
            border-bottom: 1px solid var(--border-light);
            text-align: left;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: rgba(255,255,255,0.02);
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--white);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 14px;
            transition: all 0.3s;
            background-color: var(--darker);
            color: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(126, 217, 87, 0.1);
        }
        
        select.form-control option {
            background-color: var(--darker);
            color: var(--white);
        }

        /* QUILL EDITOR DARK THEME OVERRIDES */
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: var(--border-light) !important;
            background-color: var(--darker);
        }

        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: var(--border-light) !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 16px !important;
            color: var(--white);
            background-color: var(--darker);
        }
        
        .ql-editor {
            min-height: 400px;
        }

        /* FIX PARA IMAGENS GIGANTES NO EDITOR */
        .ql-editor img {
            max-width: 100%;
            height: auto !important;
            border-radius: 8px;
            display: block;
            margin: 10px auto;
        }
        
        .ql-snow .ql-stroke {
            stroke: var(--text);
        }
        .ql-snow .ql-fill {
            fill: var(--text);
        }
        .ql-snow .ql-picker {
            color: var(--text);
        }
        .ql-snow.ql-toolbar button:hover .ql-stroke, .ql-snow .ql-toolbar button:hover .ql-stroke, .ql-snow.ql-toolbar button:focus .ql-stroke, .ql-snow .ql-toolbar button:focus .ql-stroke, .ql-snow.ql-toolbar button.ql-active .ql-stroke, .ql-snow .ql-toolbar button.ql-active .ql-stroke, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke, .ql-snow.ql-toolbar button:hover .ql-stroke-miter, .ql-snow .ql-toolbar button:hover .ql-stroke-miter, .ql-snow.ql-toolbar button:focus .ql-stroke-miter, .ql-snow .ql-toolbar button:focus .ql-stroke-miter, .ql-snow.ql-toolbar button.ql-active .ql-stroke-miter, .ql-snow .ql-toolbar button.ql-active .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke-miter, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke-miter, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke-miter {
            stroke: var(--primary);
        }
        .ql-snow.ql-toolbar button:hover .ql-fill, .ql-snow .ql-toolbar button:hover .ql-fill, .ql-snow.ql-toolbar button:focus .ql-fill, .ql-snow .ql-toolbar button:focus .ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-fill, .ql-snow .ql-toolbar button.ql-active .ql-fill, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-fill, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-fill, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-fill, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-fill, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-fill, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-fill, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-fill, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-fill, .ql-snow.ql-toolbar button:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar button:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar button:focus .ql-stroke.ql-fill, .ql-snow .ql-toolbar button:focus .ql-stroke.ql-fill, .ql-snow.ql-toolbar button.ql-active .ql-stroke.ql-fill, .ql-snow .ql-toolbar button.ql-active .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-label:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-label:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-label.ql-active .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-label.ql-active .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-item:hover .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-item:hover .ql-stroke.ql-fill, .ql-snow.ql-toolbar .ql-picker-item.ql-selected .ql-stroke.ql-fill, .ql-snow .ql-toolbar .ql-picker-item.ql-selected .ql-stroke.ql-fill {
            fill: var(--primary);
        }

        /* Responsive Mobile Admin */
        .mobile-menu-btn {
            display: none;
            background: var(--card-bg);
            color: #fff;
            border: none;
            border-bottom: 1px solid var(--border-light);
            padding: 15px 20px;
            font-size: 18px;
            cursor: pointer;
            width: 100%;
            text-align: left;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                top: 55px;
                height: calc(100vh - 55px);
            }
            .sidebar.active {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
                max-width: 100%;
                padding: 20px;
                padding-top: 80px;
            }
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            table, thead, tbody, th, td, tr {
                display: block;
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr { margin-bottom: 15px; border: 1px solid var(--border-light); border-radius: 8px; overflow: hidden; }
            td {
                border: none;
                border-bottom: 1px solid rgba(255,255,255,0.05);
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 15px;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: 600;
                text-align: left;
                content: attr(data-label);
                color: var(--text-light);
            }
        }
    </style>
</head>
<body>

<button class="mobile-menu-btn" onclick="document.querySelector('.sidebar').classList.toggle('active')">
    <i class="fas fa-bars"></i> Menu Admin
</button>

<div class="sidebar">
    <div class="sidebar-header">
        <h2>Painel <span>Admin</span></h2>
    </div>
    <ul class="nav-links">
        <li><a href="index.php"><i class="fas fa-list"></i> Listar Postagens</a></li>
        <li><a href="post_form.php"><i class="fas fa-pen-nib"></i> Nova Postagem</a></li>
        <li><a href="categories.php"><i class="fas fa-tags"></i> Categorias</a></li>
        <li><a href="../blog/index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Blog</a></li>
    </ul>
    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Sair do Sistema</a>
</div>

<div class="main-content">