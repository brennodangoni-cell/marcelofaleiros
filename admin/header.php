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
            --primary: #10b981; /* Emerald green - more premium */
            --primary-hover: #059669;
            --primary-light: #d1fae5;
            --bg-color: #f3f4f6; /* Light gray background */
            --card-bg: #ffffff;
            --border-light: #e5e7eb;
            --text-dark: #111827;
            --text-muted: #6b7280;
            --danger: #ef4444;
            --danger-hover: #dc2626;
            --danger-light: #fee2e2;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            margin: 0;
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        .sidebar {
            width: 280px;
            background-color: var(--card-bg);
            color: var(--text-dark);
            height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
            border-right: 1px solid var(--border-light);
            z-index: 100;
            box-shadow: var(--shadow-sm);
        }

        .sidebar-header {
            padding: 35px 25px;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
            background-color: var(--card-bg);
        }

        .sidebar-header h2 {
            margin: 0;
            color: var(--text-dark);
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .sidebar-header span {
            color: var(--primary);
        }

        .nav-links {
            list-style: none;
            padding: 25px 15px;
            margin: 0;
            flex-grow: 1;
        }

        .nav-links li {
            margin-bottom: 8px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: var(--text-muted);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 15px;
            font-weight: 500;
            border-radius: var(--radius-md);
        }

        .nav-links a i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .nav-links a:hover, .nav-links a.active {
            background-color: var(--primary-light);
            color: var(--primary-hover);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin: 20px;
            padding: 15px;
            background-color: var(--danger-light);
            color: var(--danger);
            text-decoration: none;
            font-weight: 600;
            border-radius: var(--radius-md);
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background-color: var(--danger);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.2);
        }

        .main-content {
            margin-left: 280px;
            padding: 40px 50px;
            flex-grow: 1;
            min-height: 100vh;
            box-sizing: border-box;
            max-width: calc(100% - 280px);
            background-color: var(--bg-color);
        }

        h1, h2, h3 {
            color: var(--text-dark);
            font-weight: 700;
            margin-top: 0;
            letter-spacing: -0.5px;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: var(--card-bg);
            padding: 30px;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 24px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 14px;
            background: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }
        .stat-info h3 {
            margin: 0 0 5px 0;
            font-size: 32px;
            color: var(--text-dark);
            line-height: 1;
        }
        .stat-info p {
            margin: 0;
            color: var(--text-muted);
            font-size: 15px;
            font-weight: 500;
        }

        .card {
            background: var(--card-bg);
            padding: 35px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 30px;
            border: 1px solid var(--border-light);
        }
        
        .card-header {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex-wrap: wrap; 
            gap: 15px;
            margin-bottom: 25px;
            border-bottom: 1px solid var(--border-light);
            padding-bottom: 20px;
        }
        
        .card-header h2 {
            margin: 0;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            background-color: var(--primary);
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2);
        }

        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
        }

        .btn-danger {
            background-color: transparent;
            border: 1px solid var(--danger);
            color: var(--danger);
            box-shadow: none;
        }

        .btn-danger:hover {
            background-color: var(--danger);
            color: #fff;
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3);
        }

        .btn-secondary {
            background-color: transparent;
            border: 1px solid #d1d5db;
            color: var(--text-dark);
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background-color: #f3f4f6;
            color: var(--text-dark);
        }

        .btn-small {
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 6px;
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 10px;
        }

        th {
            background-color: #f9fafb;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 15px 20px;
            border-bottom: 2px solid var(--border-light);
            text-align: left;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-dark);
            vertical-align: middle;
            font-size: 14px;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: inherit;
            font-size: 15px;
            transition: all 0.2s;
            background-color: #ffffff;
            color: var(--text-dark);
            box-shadow: var(--shadow-sm);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-position: right 1rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        /* QUILL EDITOR LIGHT MODERN THEME */
        .ql-toolbar {
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            border-color: #d1d5db !important;
            background-color: #f9fafb;
            padding: 12px 15px !important;
        }

        .ql-container {
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            border-color: #d1d5db !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 16px !important;
            color: var(--text-dark);
            background-color: #ffffff;
            /* box-shadow: var(--shadow-sm); */
        }
        
        .ql-editor {
            min-height: 400px;
            padding: 25px !important;
        }

        .ql-editor img {
            max-width: 100%;
            height: auto !important;
            border-radius: 12px;
            display: block;
            margin: 20px auto;
            box-shadow: var(--shadow-md);
        }

        /* Responsive Mobile Admin */
        .mobile-menu-btn {
            display: none;
            background: var(--card-bg);
            color: var(--text-dark);
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
            box-shadow: var(--shadow-sm);
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
            tr { 
                margin-bottom: 15px; 
                border: 1px solid var(--border-light); 
                border-radius: 12px; 
                overflow: hidden; 
                background: #fff;
                box-shadow: var(--shadow-sm);
            }
            td {
                border: none;
                border-bottom: 1px solid #f3f4f6;
                position: relative;
                padding-left: 50%;
                text-align: right;
            }
            td:before {
                position: absolute;
                top: 18px;
                left: 15px;
                width: 45%;
                padding-right: 10px;
                white-space: nowrap;
                font-weight: 600;
                text-align: left;
                content: attr(data-label);
                color: var(--text-muted);
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