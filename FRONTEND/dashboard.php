<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("Utilisateur non connecté");
}

$pdo = new PDO(
    'mysql:host=localhost;port=3307;dbname=projet_de_stage;charset=utf8mb4',
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $pdo->prepare("
    SELECT logo, nom, date_creation
    FROM boutique
    WHERE utilisateur_id = ?
    LIMIT 1
");

$stmt->execute([$_SESSION['user_id']]);
$boutique = $stmt->fetch(PDO::FETCH_ASSOC);

// Données de statistiques (à remplacer par vos vraies données)
$stats = [
    'vues' => 1245,
    'visiteurs' => 856,
    'conversion' => '3.8%',
    'likes' => 324,
    'revenus' => '124,500 FCFA',
    'commandes' => 48
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Creator Market</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --primary-light: #60a5fa;
            --secondary: #8b5cf6;
            --secondary-dark: #7c3aed;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --sidebar-width: 280px;
            --header-height: 70px;
            --border-radius: 16px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark Mode Variables (Default) */
        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            --bg-sidebar: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            --bg-header: rgba(30, 41, 59, 0.6);
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1a1f35 100%);
            
            --text-primary: #ffffff;
            --text-secondary: #e2e8f0;
            --text-tertiary: #cbd5e1;
            --text-muted: #94a3b8;
            
            --border-color: rgba(255, 255, 255, 0.06);
            --border-color-hover: rgba(59, 130, 246, 0.3);
            --border-color-strong: rgba(59, 130, 246, 0.15);
            
            --shadow-sm: 0 4px 16px rgba(0, 0, 0, 0.2);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.3);
            --shadow-lg: 0 12px 32px rgba(0, 0, 0, 0.3);
            
            --overlay-bg: rgba(255, 255, 255, 0.05);
            --overlay-bg-hover: rgba(255, 255, 255, 0.08);
            --overlay-border: rgba(255, 255, 255, 0.08);
        }

        /* Light Mode Variables */
        [data-theme="light"] {
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --bg-card: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            --bg-sidebar: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            --bg-header: rgba(255, 255, 255, 0.8);
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            
            --text-primary: #0f172a;
            --text-secondary: #1e293b;
            --text-tertiary: #334155;
            --text-muted: #64748b;
            
            --border-color: rgba(15, 23, 42, 0.08);
            --border-color-hover: rgba(59, 130, 246, 0.4);
            --border-color-strong: rgba(59, 130, 246, 0.2);
            
            --shadow-sm: 0 2px 8px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 4px 16px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 8px 24px rgba(15, 23, 42, 0.1);
            
            --overlay-bg: rgba(15, 23, 42, 0.03);
            --overlay-bg-hover: rgba(15, 23, 42, 0.06);
            --overlay-border: rgba(15, 23, 42, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg-primary);
            color: var(--text-secondary);
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
            transition: background 0.3s ease, color 0.3s ease;
        }

        /* Scrollbar personnalisé */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary);
        }

        /* Overlay pour mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 99;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.active {
            display: block;
            opacity: 1;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            border-right: 1px solid var(--border-color-strong);
            z-index: 100;
            overflow-y: auto;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
        }

        .logo-section {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 8px;
            background: var(--overlay-bg);
        }

        .logo-section h2 {
            color: var(--text-primary);
            font-size: 22px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            letter-spacing: -0.5px;
        }

        .logo-section h2 i {
            color: var(--primary);
            font-size: 24px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-section {
            padding: 8px 16px;
        }

        .nav-title {
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            font-weight: 600;
            padding: 20px 16px 8px;
            margin-bottom: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: var(--text-tertiary);
            text-decoration: none;
            border-radius: 12px;
            margin: 4px 0;
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(59, 130, 246, 0.1);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
            color: var(--primary-light);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 0 2px 2px 0;
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 17px;
        }

        .nav-item .notification-badge {
            position: relative;
            margin-left: auto;
            background: var(--danger);
            color: white;
            font-size: 11px;
            font-weight: 600;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .upgrade-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            margin: 20px 16px 16px;
            padding: 24px 20px;
            border-radius: var(--border-radius);
            text-align: center;
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.25);
            position: relative;
            overflow: hidden;
        }

        .upgrade-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .upgrade-card h4 {
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: 700;
            position: relative;
            z-index: 1;
            color: white;
        }

        .upgrade-card p {
            font-size: 13px;
            opacity: 0.95;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
            color: white;
        }

        .upgrade-btn {
            background: white;
            color: var(--primary);
            border: none;
            padding: 12px 20px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            font-size: 14px;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .upgrade-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .upgrade-btn:active {
            transform: translateY(0);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            background: var(--bg-gradient);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease;
        }

        /* Header */
        .main-header {
            background: var(--bg-header);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color-strong);
            padding: 16px 24px;
            position: sticky;
            top: 0;
            z-index: 90;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            min-width: 200px;
        }

        .search-bar {
            display: flex;
            align-items: center;
            background: var(--overlay-bg);
            border: 1px solid var(--overlay-border);
            border-radius: 12px;
            padding: 10px 18px;
            flex: 1;
            max-width: 450px;
            transition: var(--transition);
        }

        .search-bar:focus-within {
            background: var(--overlay-bg-hover);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-bar i {
            color: var(--text-muted);
            font-size: 16px;
        }

        .search-bar input {
            background: transparent;
            border: none;
            color: var(--text-primary);
            padding: 0 12px;
            width: 100%;
            outline: none;
            font-size: 14px;
        }

        .search-bar input::placeholder {
            color: var(--text-muted);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-btn {
            background: var(--overlay-bg);
            border: 1px solid var(--overlay-border);
            color: var(--text-primary);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
        }

        .action-btn:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        /* Theme Toggle Button */
        .theme-toggle {
            background: var(--overlay-bg);
            border: 1px solid var(--overlay-border);
            color: var(--text-primary);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .theme-toggle:hover {
            background: rgba(59, 130, 246, 0.15);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .theme-toggle i {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .theme-toggle:hover i {
            transform: rotate(180deg);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--overlay-bg);
            padding: 6px 14px 6px 6px;
            border-radius: 12px;
            border: 1px solid var(--overlay-border);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-profile:hover {
            background: var(--overlay-bg-hover);
            border-color: var(--primary);
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .user-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
        }

        .user-role {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Content Area */
        .content-area {
            padding: 24px;
        }

        /* Boutique Header */
        .boutique-header {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 32px;
            margin-bottom: 28px;
            border: 1px solid var(--border-color-strong);
            display: flex;
            align-items: center;
            gap: 28px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .boutique-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.08) 0%, transparent 70%);
            pointer-events: none;
        }

        .boutique-logo {
            width: 110px;
            height: 110px;
            border-radius: var(--border-radius);
            object-fit: cover;
            box-shadow: var(--shadow-lg);
            border: 3px solid var(--border-color-strong);
            position: relative;
            z-index: 1;
        }

        .boutique-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .boutique-info h1 {
            font-size: 32px;
            margin-bottom: 8px;
            color: var(--text-primary);
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .boutique-meta {
            color: var(--text-tertiary);
            font-size: 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 4px;
            flex-wrap: wrap;
        }

        .boutique-meta i {
            margin-right: 6px;
        }

        .meta-divider {
            margin: 0 12px;
            color: var(--text-muted);
        }

        .boutique-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            padding: 13px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(59, 130, 246, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline {
            background: var(--overlay-bg);
            color: var(--text-primary);
            border: 1.5px solid var(--overlay-border);
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-outline:hover {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }

        .btn-outline:active {
            transform: translateY(0);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 24px;
            border: 1px solid var(--border-color);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: var(--border-color-hover);
            box-shadow: var(--shadow-md);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            position: relative;
            z-index: 1;
        }

        .stat-icon.blue { 
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.25) 100%);
            color: var(--primary-light); 
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.2);
        }
        .stat-icon.green { 
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(16, 185, 129, 0.25) 100%);
            color: var(--success); 
            box-shadow: 0 4px 16px rgba(16, 185, 129, 0.2);
        }
        .stat-icon.orange { 
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.15) 0%, rgba(245, 158, 11, 0.25) 100%);
            color: var(--warning); 
            box-shadow: 0 4px 16px rgba(245, 158, 11, 0.2);
        }
        .stat-icon.red { 
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(239, 68, 68, 0.25) 100%);
            color: var(--danger); 
            box-shadow: 0 4px 16px rgba(239, 68, 68, 0.2);
        }

        .stat-trend {
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 8px;
        }

        .stat-trend.positive { 
            color: var(--success); 
            background: rgba(16, 185, 129, 0.1);
        }
        .stat-trend.negative { 
            color: var(--danger); 
            background: rgba(239, 68, 68, 0.1);
        }

        .stat-value {
            font-size: 34px;
            font-weight: 800;
            color: var(--text-primary);
            margin: 12px 0 8px;
            letter-spacing: -1px;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            color: var(--text-tertiary);
            font-size: 14px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        .section-card {
            background: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 28px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-sm);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: var(--text-primary);
        }

        .section-title a {
            color: var(--primary-light);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
        }

        .section-title a:hover {
            color: var(--primary);
            text-decoration: underline;
        }

        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }

        .orders-table thead {
            background: var(--overlay-bg);
        }

        .orders-table th {
            text-align: left;
            padding: 14px 16px;
            color: var(--text-tertiary);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .orders-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
            color: var(--text-secondary);
        }

        .orders-table tbody tr {
            transition: var(--transition);
        }

        .orders-table tbody tr:hover {
            background: var(--overlay-bg);
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .status-badge.pending { 
            background: rgba(245, 158, 11, 0.15); 
            color: var(--warning); 
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .status-badge.completed { 
            background: rgba(16, 185, 129, 0.15); 
            color: var(--success); 
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .status-badge.processing { 
            background: rgba(59, 130, 246, 0.15); 
            color: var(--primary-light); 
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .quick-action-btn {
            background: var(--overlay-bg);
            border: 1px solid var(--overlay-border);
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .quick-action-btn:hover {
            background: rgba(59, 130, 246, 0.1);
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .quick-action-btn:active {
            transform: translateY(0);
        }

        .quick-action-btn i {
            font-size: 26px;
            margin-bottom: 12px;
            display: block;
        }

        .quick-action-btn div {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-tertiary);
        }

        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 1001;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border: none;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            transition: var(--transition);
        }

        .mobile-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 24px rgba(59, 130, 246, 0.5);
        }

        .mobile-toggle:active {
            transform: scale(0.95);
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .activity-item {
            display: flex;
            gap: 14px;
            padding: 14px;
            background: var(--overlay-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }

        .activity-item:hover {
            background: var(--overlay-bg-hover);
            border-color: var(--border-color-hover);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            font-size: 14px;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .activity-time {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* Progress Bar */
        .progress-container {
            margin-top: 16px;
        }

        .progress-item {
            margin-bottom: 20px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .progress-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .progress-value {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-light);
        }

        .progress-bar {
            height: 8px;
            background: var(--overlay-bg);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 4px;
            transition: width 1s ease;
        }

        /* Responsive Design */
        @media (max-width: 1400px) {
            .content-grid {
                grid-template-columns: 1fr 340px;
            }
        }

        @media (max-width: 1200px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 1024px) {
            :root {
                --sidebar-width: 0;
            }

            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .boutique-header {
                flex-direction: column;
                text-align: center;
                padding: 28px 24px;
            }
            
            .boutique-actions {
                justify-content: center;
                width: 100%;
            }

            .main-header {
                padding: 16px 20px 16px 72px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .content-area {
                padding: 16px;
            }

            .header-actions {
                gap: 8px;
            }

            .user-info {
                display: none;
            }

            .search-bar {
                max-width: 100%;
            }

            .section-card {
                padding: 20px;
            }

            .orders-table-wrapper {
                overflow-x: auto;
                margin: 0 -20px;
                padding: 0 20px;
            }

            .orders-table {
                min-width: 600px;
            }

            .boutique-info h1 {
                font-size: 26px;
            }

            .stat-value {
                font-size: 28px;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .btn-primary, .btn-outline {
                padding: 12px 18px;
                font-size: 13px;
                width: 100%;
                justify-content: center;
            }

            .boutique-actions {
                flex-direction: column;
                width: 100%;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .action-btn, .theme-toggle {
                width: 38px;
                height: 38px;
            }

            .main-header {
                padding: 12px 16px 12px 68px;
            }
        }

        @media (max-width: 480px) {
            .boutique-header {
                padding: 20px 16px;
            }

            .boutique-logo {
                width: 90px;
                height: 90px;
            }

            .boutique-info h1 {
                font-size: 22px;
            }

            .stat-card {
                padding: 20px;
            }

            .section-title {
                font-size: 18px;
            }

            .content-area {
                padding: 12px;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { 
                opacity: 0; 
                transform: translateY(30px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from { 
                opacity: 0; 
                transform: translateX(-30px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) backwards;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        .slide-in-left {
            animation: slideInLeft 0.5s cubic-bezier(0.4, 0, 0.2, 1) backwards;
        }

        /* Stagger animation delays */
        .stats-grid .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stats-grid .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stats-grid .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stats-grid .stat-card:nth-child(4) { animation-delay: 0.4s; }

        /* Loading states */
        .skeleton {
            background: linear-gradient(90deg, var(--overlay-bg) 25%, var(--overlay-bg-hover) 50%, var(--overlay-bg) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body data-theme="dark">
    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo-section">
            <h2>
                <i class="fas fa-envelope"></i>
                Creator Market
            </h2>
        </div>
        
        <div class="nav-section">
            <div class="nav-title">Principal</div>
            <a href="#" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-store"></i>
                <span>Ma Boutique</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-box"></i>
                <span>Produits</span>
                <span class="notification-badge">3</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Commandes</span>
                <span class="notification-badge">5</span>
            </a>
            
            <div class="nav-title">Marketing</div>
            <a href="#" class="nav-item">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-bullhorn"></i>
                <span>Promotions</span>
            </a>
            
            <div class="nav-title">Paramètres</div>
            <a href="#" class="nav-item">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-user"></i>
                <span>Profil</span>
            </a>
            <a href="#" class="nav-item">
                <i class="fas fa-question-circle"></i>
                <span>Aide & Support</span>
            </a>
        </div>
        
        <div class="upgrade-card">
            <h4>✨ Passez à Premium</h4>
            <p>Obtenez 2x plus de clients</p>
            <button class="upgrade-btn" onclick="upgradePremium()">
                <i class="fas fa-crown"></i>
                <span>Découvrir Premium</span>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher produits, commandes...">
                </div>
            </div>
            
            <div class="header-actions">
                <button class="theme-toggle" onclick="toggleTheme()" title="Changer le thème">
                    <i class="fas fa-moon" id="themeIcon"></i>
                </button>

                <button class="action-btn" onclick="toggleLanguage()" title="Changer la langue">
                    <i class="fas fa-globe"></i>
                </button>
                
                <div style="position: relative;">
                    <button class="action-btn" onclick="openNotifications()">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge" style="position: absolute; top: -5px; right: -5px;">3</span>
                    </button>
                </div>
                
                <button class="action-btn" onclick="openWhatsApp()" title="WhatsApp">
                    <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                </button>
                
                <div class="user-profile">
                    <?php if ($boutique && $boutique['logo']): ?>
                        <img src="<?= htmlspecialchars($boutique['logo']) ?>" alt="Logo boutique" class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-store" style="color: white;"></i>
                        </div>
                    <?php endif; ?>
                    <div class="user-info">
                        <div class="user-name"><?= $boutique ? htmlspecialchars($boutique['nom']) : 'Ma Boutique' ?></div>
                        <div class="user-role">Propriétaire</div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            <!-- Boutique Header -->
            <div class="boutique-header fade-in">
                <?php if ($boutique && $boutique['logo']): ?>
                    <img src="<?= htmlspecialchars($boutique['logo']) ?>" alt="Logo boutique" class="boutique-logo">
                <?php else: ?>
                    <div class="boutique-logo" style="background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%); display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-store" style="font-size: 44px; color: white;"></i>
                    </div>
                <?php endif; ?>
                
                <div class="boutique-info">
                    <h1><?= $boutique ? htmlspecialchars($boutique['nom']) : 'Ma Boutique' ?></h1>
                    <div class="boutique-meta">
                        <span><i class="fas fa-calendar"></i> Créée le <?= $boutique ? htmlspecialchars($boutique['date_creation']) : date('d/m/Y') ?></span>
                        <span class="meta-divider">•</span>
                        <span><i class="fas fa-star" style="color: var(--warning);"></i> Boutique Active</span>
                    </div>
                    
                    <div class="boutique-actions">
                        <button class="btn-primary" onclick="window.location.href='ajout_produits.html'">
                            <i class="fas fa-plus"></i>
                            <span>Ajouter un produit</span>
                        </button>
                        <button class="btn-outline" onclick="window.location.href='index.php'">
                            <i class="fas fa-eye"></i>
                            <span>Voir ma boutique</span>
                        </button>
                        <button class="btn-outline" onclick="shareBoutique()">
                            <i class="fas fa-share-alt"></i>
                            <span>Partager</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card fade-in-up">
                    <div class="stat-header">
                        <div class="stat-icon blue">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            12.5%
                        </div>
                    </div>
                    <div class="stat-value"><?= number_format($stats['vues']) ?></div>
                    <div class="stat-label">Total de vues</div>
                </div>
                
                <div class="stat-card fade-in-up">
                    <div class="stat-header">
                        <div class="stat-icon green">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            8.3%
                        </div>
                    </div>
                    <div class="stat-value"><?= number_format($stats['visiteurs']) ?></div>
                    <div class="stat-label">Visiteurs uniques</div>
                </div>
                
                <div class="stat-card fade-in-up">
                    <div class="stat-header">
                        <div class="stat-icon orange">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            2.1%
                        </div>
                    </div>
                    <div class="stat-value"><?= $stats['conversion'] ?></div>
                    <div class="stat-label">Taux de conversion</div>
                </div>
                
                <div class="stat-card fade-in-up">
                    <div class="stat-header">
                        <div class="stat-icon red">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-trend positive">
                            <i class="fas fa-arrow-up"></i>
                            15.7%
                        </div>
                    </div>
                    <div class="stat-value"><?= number_format($stats['likes']) ?></div>
                    <div class="stat-label">J'aime reçus</div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="content-grid">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Commandes Récentes -->
                    <div class="section-card fade-in-up">
                        <div class="section-title">
                            <span>📦 Commandes récentes</span>
                            <a href="#">Voir tout <i class="fas fa-arrow-right"></i></a>
                        </div>
                        
                        <div class="orders-table-wrapper">
                            <table class="orders-table">
                                <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>#CM-001</strong></td>
                                        <td>Jean Dupont</td>
                                        <td><strong>25,000 FCFA</strong></td>
                                        <td><span class="status-badge completed">Livrée</span></td>
                                        <td>15/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#CM-002</strong></td>
                                        <td>Marie Martin</td>
                                        <td><strong>18,500 FCFA</strong></td>
                                        <td><span class="status-badge processing">En cours</span></td>
                                        <td>14/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#CM-003</strong></td>
                                        <td>Pierre Durand</td>
                                        <td><strong>32,000 FCFA</strong></td>
                                        <td><span class="status-badge pending">En attente</span></td>
                                        <td>13/03/2024</td>
                                    </tr>
                                    <tr>
                                        <td><strong>#CM-004</strong></td>
                                        <td>Sophie Bernard</td>
                                        <td><strong>15,750 FCFA</strong></td>
                                        <td><span class="status-badge completed">Livrée</span></td>
                                        <td>12/03/2024</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column -->
                <div class="right-column">
                    <!-- Actions Rapides -->
                    <div class="section-card fade-in-up" style="animation-delay: 0.1s;">
                        <div class="section-title">
                            <span>⚡ Actions rapides</span>
                        </div>
                        
                        <div class="quick-actions">
                            <div class="quick-action-btn" onclick="window.location.href='ajout_produits.html'">
                                <i class="fas fa-plus" style="color: var(--success);"></i>
                                <div>Ajouter produit</div>
                            </div>
                            <div class="quick-action-btn" onclick="window.location.href='#'">
                                <i class="fas fa-edit" style="color: var(--primary);"></i>
                                <div>Modifier boutique</div>
                            </div>
                            <div class="quick-action-btn" onclick="window.location.href='#'">
                                <i class="fas fa-chart-bar" style="color: var(--warning);"></i>
                                <div>Voir analytics</div>
                            </div>
                            <div class="quick-action-btn" onclick="shareBoutique()">
                                <i class="fas fa-share-alt" style="color: var(--secondary);"></i>
                                <div>Partager</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Activité Récente -->
                    <div class="section-card fade-in-up" style="margin-top: 24px; animation-delay: 0.2s;">
                        <div class="section-title">
                            <span>🔔 Activité récente</span>
                        </div>
                        
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon" style="background: rgba(16, 185, 129, 0.15); color: var(--success);">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Nouvelle commande reçue</div>
                                    <div class="activity-time">Il y a 2 heures</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon" style="background: rgba(59, 130, 246, 0.15); color: var(--primary);">
                                    <i class="fas fa-box"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">Produit ajouté avec succès</div>
                                    <div class="activity-time">Il y a 5 heures</div>
                                </div>
                            </div>
                            
                            <div class="activity-item">
                                <div class="activity-icon" style="background: rgba(139, 92, 246, 0.15); color: var(--secondary);">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">12 nouveaux j'aime</div>
                                    <div class="activity-time">Aujourd'hui</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Objectifs -->
                    <div class="section-card fade-in-up" style="margin-top: 24px; animation-delay: 0.3s;">
                        <div class="section-title">
                            <span>🎯 Objectifs du mois</span>
                        </div>
                        
                        <div class="progress-container">
                            <div class="progress-item">
                                <div class="progress-header">
                                    <span class="progress-label">Revenus</span>
                                    <span class="progress-value">65%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 65%;"></div>
                                </div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-header">
                                    <span class="progress-label">Commandes</span>
                                    <span class="progress-value">48/100</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 48%;"></div>
                                </div>
                            </div>
                            
                            <div class="progress-item">
                                <div class="progress-header">
                                    <span class="progress-label">Nouveaux clients</span>
                                    <span class="progress-value">82%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: 82%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle Function
        function toggleTheme() {
            const body = document.body;
            const themeIcon = document.getElementById('themeIcon');
            const currentTheme = body.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            body.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Update icon
            if (newTheme === 'light') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            } else {
                themeIcon.classList.remove('fa-sun');
                themeIcon.classList.add('fa-moon');
            }
            
            showNotification(newTheme === 'light' ? '☀️ Mode clair activé' : '🌙 Mode sombre activé');
        }

        // Load saved theme on page load
        document.addEventListener('DOMContentLoaded', function() {
            const savedTheme = localStorage.getItem('theme') || 'dark';
            const themeIcon = document.getElementById('themeIcon');
            
            document.body.setAttribute('data-theme', savedTheme);
            
            if (savedTheme === 'light') {
                themeIcon.classList.remove('fa-moon');
                themeIcon.classList.add('fa-sun');
            }
        });

        // Toggle sidebar on mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            // Prevent body scroll when sidebar is open
            document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
        }

        // Close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        // Upgrade to premium
        function upgradePremium() {
            const whatsapp = `https://wa.me/237657300644?text=Bonjour, je suis intéressé par votre offre premium !`;
            window.open(whatsapp, '_blank');
        }

        // Share boutique
        function shareBoutique() {
            if (navigator.share) {
                navigator.share({
                    title: 'Ma Boutique - Creator Market',
                    text: 'Découvrez ma boutique sur Creator Market !',
                    url: window.location.href
                }).catch(err => {
                    console.log('Erreur lors du partage:', err);
                    copyToClipboard();
                });
            } else {
                copyToClipboard();
            }
        }

        function copyToClipboard() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showNotification('📋 Lien copié dans le presse-papier !');
            });
        }

        // Show notification
        function showNotification(message) {
            // Créer une notification toast simple
            const notification = document.createElement('div');
            notification.textContent = message;
            notification.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: white;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
                z-index: 10000;
                font-weight: 600;
                animation: slideInUp 0.3s ease;
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutDown 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Open WhatsApp
        function openWhatsApp() {
            const whatsapp = `https://wa.me/237657300644?text=Bonjour, j'ai une question concernant ma boutique.`;
            window.open(whatsapp, '_blank');
        }

        // Toggle language
        function toggleLanguage() {
            showNotification('🌍 Changement de langue - Fonctionnalité à venir');
        }

        // Open notifications
        function openNotifications() {
            showNotification('🔔 Notifications - Fonctionnalité à venir');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');
            
            if (window.innerWidth <= 1024 && 
                !sidebar.contains(event.target) && 
                !mobileToggle.contains(event.target) && 
                sidebar.classList.contains('active')) {
                closeSidebar();
            }
        });

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 1024) {
                    closeSidebar();
                }
            }, 250);
        });

        // Animate progress bars on load
        window.addEventListener('load', function() {
            const progressBars = document.querySelectorAll('.progress-fill');
            progressBars.forEach((bar, index) => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 100 + (index * 100));
            });
        });

        // Add keyboard navigation
        document.addEventListener('keydown', function(event) {
            // Escape key closes sidebar
            if (event.key === 'Escape') {
                closeSidebar();
            }
            // Ctrl/Cmd + K toggles theme
            if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
                event.preventDefault();
                toggleTheme();
            }
        });

        // Add CSS for toast animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInUp {
                from {
                    opacity: 0;
                    transform: translateY(100%);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            @keyframes slideOutDown {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(100%);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>