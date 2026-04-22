<?php
// Force UTF-8 content type — fixes ₹ symbol on shared hosting servers
if (!headers_sent()) {
    header('Content-Type: text/html; charset=UTF-8');
}
// Ensure security functions are available
if (!function_exists('generateCsrfToken')) {
    require_once __DIR__ . '/../../includes/security.php';
}
// Ensure database class is available
if (!class_exists('Database')) {
    require_once __DIR__ . '/../../includes/db.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Admin Panel' ?> - <?= SITE_NAME ?></title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="<?= generateCsrfToken() ?>">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* =============================================
           DESIGN TOKENS
        ============================================= */
        :root {
            --sidebar-width: 265px;
            --header-height: 68px;

            /* Sidebar */
            --sidebar-bg: #0f1117;
            --sidebar-border: rgba(255,255,255,0.06);
            --sidebar-text: rgba(255,255,255,0.55);
            --sidebar-text-hover: rgba(255,255,255,0.9);
            --sidebar-active-bg: rgba(99,102,241,0.15);
            --sidebar-active-text: #a5b4fc;
            --sidebar-active-bar: #6366f1;

            /* Content */
            --content-bg: #f8f9fc;
            --card-bg: #ffffff;
            --card-border: #e8ecf0;
            --card-radius: 14px;
            --card-shadow: 0 2px 12px rgba(0,0,0,0.06);

            /* Colors */
            --accent: #6366f1;
            --accent-light: #e0e7ff;
            --text-primary: #1a1e2e;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border: #e2e8f0;

            /* Status */
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
        }

        /* =============================================
           BASE
        ============================================= */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--content-bg);
            color: var(--text-primary);
            font-size: 0.925rem;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* =============================================
           SIDEBAR
        ============================================= */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1050;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.4rem;
            border-bottom: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(99,102,241,0.4);
        }

        .sidebar-brand-text {
            font-size: 0.9rem;
            font-weight: 800;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            line-height: 1.2;
        }

        .sidebar-brand-sub {
            font-size: 0.65rem;
            color: var(--sidebar-text);
            font-weight: 400;
            letter-spacing: 0;
            text-transform: none;
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem 0.75rem;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        .sidebar-section-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255,255,255,0.25);
            padding: 0.5rem 0.75rem 0.4rem;
            margin-top: 0.5rem;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 0.6rem 0.875rem;
            border-radius: 10px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 2px;
            position: relative;
            transition: all 0.18s ease;
        }

        .sidebar-nav-link:hover {
            color: var(--sidebar-text-hover);
            background: rgba(255,255,255,0.05);
        }

        .sidebar-nav-link.active {
            color: var(--sidebar-active-text);
            background: var(--sidebar-active-bg);
            font-weight: 600;
        }

        .sidebar-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: var(--sidebar-active-bar);
            border-radius: 0 4px 4px 0;
        }

        .sidebar-nav-link .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 0.875rem;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .sidebar-nav-link .nav-badge {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 1px 6px;
            border-radius: 20px;
            background: rgba(99,102,241,0.25);
            color: #a5b4fc;
            font-weight: 600;
        }

        .sidebar-divider {
            height: 1px;
            background: var(--sidebar-border);
            margin: 0.75rem 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid var(--sidebar-border);
            flex-shrink: 0;
        }

        .sidebar-admin-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.6rem 0.875rem;
            border-radius: 10px;
            background: rgba(255,255,255,0.04);
        }

        .sidebar-admin-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: white;
            flex-shrink: 0;
            overflow: hidden;
        }

        .sidebar-admin-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .sidebar-admin-name {
            font-size: 0.8rem;
            font-weight: 600;
            color: rgba(255,255,255,0.85);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-admin-role {
            font-size: 0.67rem;
            color: var(--sidebar-text);
        }

        .sidebar-logout-btn {
            margin-left: auto;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(239,68,68,0.1);
            border: none;
            color: #fca5a5;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.18s;
            flex-shrink: 0;
        }

        .sidebar-logout-btn:hover {
            background: rgba(239,68,68,0.25);
            color: #f87171;
        }

        /* =============================================
           TOPBAR / HEADER
        ============================================= */
        .admin-header {
            height: var(--header-height);
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 1.75rem;
            transition: left 0.3s cubic-bezier(0.4,0,0.2,1);
            gap: 1rem;
        }

        .header-hamburger {
            display: none;
            background: none;
            border: none;
            padding: 0.4rem;
            border-radius: 8px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.18s;
        }

        .header-hamburger:hover {
            background: var(--content-bg);
            color: var(--text-primary);
        }

        .header-search {
            flex: 1;
            max-width: 340px;
        }

        .header-search-input {
            background: var(--content-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 0.45rem 1rem 0.45rem 2.5rem;
            font-size: 0.85rem;
            width: 100%;
            color: var(--text-primary);
            transition: all 0.2s;
            outline: none;
        }

        .header-search-input:focus {
            background: white;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
        }

        .header-search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.8rem;
            pointer-events: none;
        }

        .header-actions {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .header-icon-btn {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: var(--content-bg);
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            text-decoration: none;
            transition: all 0.18s;
            font-size: 0.875rem;
            position: relative;
        }

        .header-icon-btn:hover {
            background: var(--accent-light);
            border-color: var(--accent);
            color: var(--accent);
        }

        .header-notif-dot {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 7px;
            height: 7px;
            background: var(--danger);
            border-radius: 50%;
            border: 1.5px solid white;
        }

        .header-divider {
            width: 1px;
            height: 28px;
            background: var(--border);
        }

        .header-user-btn {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            background: none;
            border: none;
            padding: 0.3rem 0.5rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.18s;
        }

        .header-user-btn:hover {
            background: var(--content-bg);
        }

        .header-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #818cf8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 700;
            color: white;
            overflow: hidden;
            flex-shrink: 0;
        }

        .header-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        /* =============================================
           MAIN CONTENT
        ============================================= */
        .admin-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 1.75rem;
            min-height: calc(100vh - var(--header-height));
            transition: margin-left 0.3s cubic-bezier(0.4,0,0.2,1);
        }

        /* =============================================
           PAGE HEADER
        ============================================= */
        .page-header {
            margin-bottom: 1.75rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.5px;
            margin: 0;
            line-height: 1.2;
        }

        .page-subtitle {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0.2rem 0 0;
        }

        /* =============================================
           CARDS
        ============================================= */
        .admin-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--card-radius);
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .admin-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--card-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .admin-card-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .admin-card-body {
            padding: 1.5rem;
        }

        /* Stat Cards */
        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--card-radius);
            padding: 1.4rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
        }

        .stat-card.stat-indigo::after { background: linear-gradient(90deg, #6366f1, #818cf8); }
        .stat-card.stat-emerald::after { background: linear-gradient(90deg, #10b981, #34d399); }
        .stat-card.stat-amber::after { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .stat-card.stat-rose::after { background: linear-gradient(90deg, #ef4444, #f87171); }
        .stat-card.stat-blue::after { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
        .stat-card.stat-violet::after { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }

        .stat-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .stat-icon.bg-indigo { background: #e0e7ff; color: #6366f1; }
        .stat-icon.bg-emerald { background: #d1fae5; color: #10b981; }
        .stat-icon.bg-amber { background: #fef3c7; color: #f59e0b; }
        .stat-icon.bg-rose { background: #fee2e2; color: #ef4444; }
        .stat-icon.bg-blue { background: #dbeafe; color: #3b82f6; }
        .stat-icon.bg-violet { background: #ede9fe; color: #8b5cf6; }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.3rem;
        }

        .stat-value {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            letter-spacing: -0.5px;
        }

        .stat-change {
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        /* =============================================
           TABLES
        ============================================= */
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .admin-table thead th {
            background: #f8f9fc;
            color: var(--text-secondary);
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .admin-table thead th:first-child { padding-left: 1.5rem; border-radius: 0; }
        .admin-table thead th:last-child { padding-right: 1.5rem; }

        .admin-table tbody td {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
            color: var(--text-primary);
        }

        .admin-table tbody td:first-child { padding-left: 1.5rem; }
        .admin-table tbody td:last-child { padding-right: 1.5rem; }

        .admin-table tbody tr:last-child td { border-bottom: none; }

        .admin-table tbody tr {
            transition: background 0.15s;
        }

        .admin-table tbody tr:hover {
            background: #fafbff;
        }

        /* =============================================
           BADGES
        ============================================= */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
            opacity: 0.8;
        }

        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .badge-indigo { background: #e0e7ff; color: #3730a3; }
        .badge-violet { background: #ede9fe; color: #5b21b6; }

        /* =============================================
           BUTTONS
        ============================================= */
        .btn-accent {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 9px;
            padding: 0.5rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.18s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-accent:hover {
            background: #4f46e5;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99,102,241,0.35);
        }

        .btn-outline-accent {
            background: transparent;
            color: var(--accent);
            border: 1.5px solid var(--accent);
            border-radius: 9px;
            padding: 0.45rem 1.1rem;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.18s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-outline-accent:hover {
            background: var(--accent);
            color: white;
        }

        .btn-ghost {
            background: var(--content-bg);
            color: var(--text-secondary);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 0.45rem 1.1rem;
            font-size: 0.82rem;
            font-weight: 500;
            transition: all 0.18s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-ghost:hover {
            background: white;
            color: var(--text-primary);
            border-color: #d1d5db;
        }

        .btn-danger-ghost {
            background: transparent;
            color: var(--danger);
            border: 1.5px solid #fecaca;
            border-radius: 9px;
            padding: 0.4rem 0.9rem;
            font-size: 0.82rem;
            font-weight: 600;
            transition: all 0.18s;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
        }

        .btn-danger-ghost:hover {
            background: #fee2e2;
            border-color: var(--danger);
        }

        /* =============================================
           FORM CONTROLS
        ============================================= */
        .form-control, .form-select {
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 0.55rem 0.9rem;
            font-size: 0.875rem;
            color: var(--text-primary);
            background: white;
            transition: all 0.18s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.1);
            outline: none;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }

        /* Search bar */
        .search-bar {
            position: relative;
        }

        .search-bar .search-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.82rem;
            pointer-events: none;
        }

        .search-bar input {
            padding-left: 2.5rem;
        }

        /* =============================================
           MODAL
        ============================================= */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .modal-header {
            padding: 1.3rem 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border);
            background: #fafbff;
            border-radius: 0 0 16px 16px;
        }

        /* =============================================
           ALERTS
        ============================================= */
        .alert {
            border-radius: 10px;
            border: none;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* =============================================
           AVATAR INITIAL COLORS
        ============================================= */
        .avatar-a, .avatar-b, .avatar-c { background: #dbeafe; color: #1d4ed8; }
        .avatar-d, .avatar-e, .avatar-f { background: #d1fae5; color: #065f46; }
        .avatar-g, .avatar-h, .avatar-i { background: #fef3c7; color: #92400e; }
        .avatar-j, .avatar-k, .avatar-l { background: #ede9fe; color: #5b21b6; }
        .avatar-m, .avatar-n, .avatar-o { background: #fce7f3; color: #9d174d; }
        .avatar-p, .avatar-q, .avatar-r { background: #e0e7ff; color: #3730a3; }
        .avatar-s, .avatar-t, .avatar-u { background: #d1fae5; color: #065f46; }
        .avatar-v, .avatar-w, .avatar-x { background: #fee2e2; color: #991b1b; }
        .avatar-y, .avatar-z { background: #fef9c3; color: #854d0e; }

        /* =============================================
           MOBILE / RESPONSIVE
        ============================================= */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(3px);
            z-index: 1040;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        @media (max-width: 991.98px) {
            .admin-sidebar {
                transform: translateX(calc(-1 * var(--sidebar-width)));
            }

            .admin-sidebar.show {
                transform: translateX(0);
                box-shadow: 8px 0 30px rgba(0,0,0,0.3);
            }

            .admin-header {
                left: 0 !important;
                padding: 0 1rem;
            }

            .admin-content {
                margin-left: 0 !important;
                padding: 1.25rem 1rem;
            }

            .header-hamburger {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .header-search {
                display: none;
            }

            .stat-card {
                padding: 1.1rem;
            }

            .stat-value {
                font-size: 1.4rem;
            }
        }

        @media (max-width: 575.98px) {
            .page-title { font-size: 1.2rem; }
            .admin-content { padding: 1rem 0.875rem; }
            .header-user-name { display: none; }
        }

        /* =============================================
           ANIMATIONS
        ============================================= */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: fadeInUp 0.35s ease forwards;
        }

        .animate-in:nth-child(1) { animation-delay: 0.05s; }
        .animate-in:nth-child(2) { animation-delay: 0.1s; }
        .animate-in:nth-child(3) { animation-delay: 0.15s; }
        .animate-in:nth-child(4) { animation-delay: 0.2s; }

        /* =============================================
           MISC UTILITIES
        ============================================= */
        .text-indigo { color: #6366f1 !important; }
        .text-emerald { color: #10b981 !important; }
        .text-amber { color: #f59e0b !important; }
        .text-rose { color: #ef4444 !important; }

        /* Override Bootstrap btn-primary to use accent */
        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
            border-radius: 9px;
            font-weight: 600;
            font-size: 0.875rem;
            padding: 0.5rem 1.25rem;
        }

        .btn-primary:hover {
            background: #4f46e5 !important;
            border-color: #4f46e5 !important;
        }

        /* Dropdown */
        .dropdown-menu {
            border: 1px solid var(--border);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            padding: 0.4rem;
            font-size: 0.875rem;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 0.5rem 0.875rem;
            color: var(--text-primary);
            font-weight: 500;
            transition: all 0.15s;
        }

        .dropdown-item:hover {
            background: var(--content-bg);
            color: var(--accent);
        }

        .dropdown-item.text-danger:hover {
            background: #fee2e2;
            color: var(--danger) !important;
        }
    </style>
</head>
<body>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop d-lg-none" id="sidebarBackdrop" onclick="toggleAdminSidebar()"></div>

    <!-- Admin Header -->
    <header class="admin-header">
        <!-- Hamburger (mobile) -->
        <button type="button" class="header-hamburger" onclick="toggleAdminSidebar()" aria-label="Toggle Sidebar">
            <i class="fas fa-bars fa-lg"></i>
        </button>

        <!-- Search -->
        <div class="header-search d-none d-md-flex position-relative">
            <i class="fas fa-search header-search-icon"></i>
            <input type="text" class="header-search-input" placeholder="Search anything...">
        </div>

        <!-- Right Actions -->
        <div class="header-actions">
            <!-- Visit Store -->
            <a href="<?= BASE_URL ?>" class="header-icon-btn d-none d-sm-flex" target="_blank" title="Visit Store">
                <i class="fas fa-store"></i>
            </a>

            <!-- Notifications -->
            <div class="header-icon-btn position-relative" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="header-notif-dot"></span>
            </div>

            <div class="header-divider"></div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="header-user-btn dropdown-toggle" data-bs-toggle="dropdown" type="button">
                    <div class="header-user-avatar">
                        <?php if (!empty($_SESSION['user_profile_photo'])): ?>
                            <img src="<?= BASE_URL ?>uploads/profiles/<?= $_SESSION['user_profile_photo'] ?>" alt="Admin">
                        <?php else: ?>
                            <?= strtoupper(substr($_SESSION['user_email'] ?? 'A', 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <span class="header-user-name d-none d-md-inline">
                        <?= htmlspecialchars(explode('@', $_SESSION['user_email'] ?? 'admin')[0]) ?>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>admin/profile.php">
                            <i class="fas fa-user-circle me-2 text-indigo"></i> My Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="<?= BASE_URL ?>" target="_blank">
                            <i class="fas fa-external-link-alt me-2 text-emerald"></i> Visit Store
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item text-danger" href="<?= BASE_URL ?>user/logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Sidebar Toggle Script (must be before sidebar renders) -->
    <script>
        function toggleAdminSidebar() {
            var sidebar = document.querySelector('.admin-sidebar');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (sidebar) sidebar.classList.toggle('show');
            if (backdrop) backdrop.classList.toggle('show');
            document.body.style.overflow = (sidebar && sidebar.classList.contains('show')) ? 'hidden' : '';
        }
    </script>
