<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — POS Jeruk Lokal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #e85d04;
            --primary-dark: #c44d00;
            --secondary: #2d6a4f;
            --sidebar-w: 250px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f5f5; color: #333; }

        /* Sidebar */
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-w); background: #1a1a2e;
            color: #fff; z-index: 1000; transition: transform .3s;
            overflow-y: auto;
        }
        .sidebar.hidden { transform: translateX(-100%); }
        .sidebar-brand {
            padding: 20px 16px; background: var(--primary);
            font-size: 1.1rem; font-weight: 700; display: flex;
            align-items: center; gap: 10px;
        }
        .sidebar-brand img { width: 32px; height: 32px; border-radius: 50%; }
        .sidebar-menu { padding: 12px 0; }
        .menu-label {
            padding: 8px 16px 4px; font-size: .7rem;
            text-transform: uppercase; color: #888; letter-spacing: 1px;
        }
        .menu-item a {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; color: #ccc; text-decoration: none;
            transition: all .2s; font-size: .9rem;
        }
        .menu-item a:hover, .menu-item a.active {
            background: rgba(255,255,255,.1); color: #fff;
            border-left: 3px solid var(--primary);
        }
        .menu-item a i { width: 18px; text-align: center; }

        /* Topbar */
        .topbar {
            position: fixed; top: 0; left: var(--sidebar-w); right: 0;
            height: 60px; background: #fff; box-shadow: 0 1px 4px rgba(0,0,0,.1);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 20px; z-index: 999; transition: left .3s;
        }
        .topbar.full { left: 0; }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .btn-toggle {
            background: none; border: none; font-size: 1.2rem;
            cursor: pointer; color: #555; padding: 4px 8px;
        }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .user-info { font-size: .85rem; text-align: right; }
        .user-info strong { display: block; }
        .user-info span { color: #888; font-size: .75rem; }
        .btn-logout {
            background: var(--primary); color: #fff; border: none;
            padding: 6px 14px; border-radius: 6px; cursor: pointer;
            font-size: .85rem; text-decoration: none; display: inline-flex;
            align-items: center; gap: 6px;
        }
        .btn-logout:hover { background: var(--primary-dark); }

        /* Main content */
        .main-content {
            margin-left: var(--sidebar-w); margin-top: 60px;
            padding: 24px; min-height: calc(100vh - 60px);
            transition: margin-left .3s;
        }
        .main-content.full { margin-left: 0; }

        /* Cards */
        .card {
            background: #fff; border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06); overflow: hidden;
        }
        .card-header {
            padding: 16px 20px; border-bottom: 1px solid #f0f0f0;
            display: flex; align-items: center; justify-content: space-between;
        }
        .card-header h5 { font-size: 1rem; font-weight: 600; }
        .card-body { padding: 20px; }

        /* Stats */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px; margin-bottom: 24px;
        }
        .stat-card {
            background: #fff; border-radius: 12px; padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            display: flex; align-items: center; gap: 16px;
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.4rem; color: #fff; flex-shrink: 0;
        }
        .stat-info h3 { font-size: 1.4rem; font-weight: 700; }
        .stat-info p { font-size: .8rem; color: #888; margin-top: 2px; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: .875rem; }
        thead th {
            background: #f8f9fa; padding: 12px 16px;
            text-align: left; font-weight: 600; color: #555;
            border-bottom: 2px solid #e9ecef;
        }
        tbody td { padding: 12px 16px; border-bottom: 1px solid #f0f0f0; }
        tbody tr:hover { background: #fafafa; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; border-radius: 8px; border: none;
            cursor: pointer; font-size: .875rem; font-weight: 500;
            text-decoration: none; transition: all .2s;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-secondary { background: #6c757d; color: #fff; }
        .btn-secondary:hover { background: #5a6268; }
        .btn-danger { background: #dc3545; color: #fff; }
        .btn-danger:hover { background: #c82333; }
        .btn-success { background: #28a745; color: #fff; }
        .btn-success:hover { background: #218838; }
        .btn-sm { padding: 5px 10px; font-size: .8rem; }
        .btn-outline {
            background: transparent; border: 1px solid currentColor;
        }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: .875rem; }
        .form-control {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd;
            border-radius: 8px; font-size: .875rem; transition: border .2s;
            background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232,93,4,.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }

        /* Badge */
        .badge {
            display: inline-block; padding: 3px 8px; border-radius: 20px;
            font-size: .75rem; font-weight: 600;
        }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }

        /* Alert */
        .alert {
            padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-warning { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }

        /* Pagination */
        .pagination { display: flex; gap: 4px; justify-content: center; margin-top: 16px; flex-wrap: wrap; }
        .pagination a, .pagination span {
            padding: 6px 12px; border-radius: 6px; font-size: .85rem;
            text-decoration: none; border: 1px solid #ddd; color: #555;
        }
        .pagination .active span { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination a:hover { background: #f0f0f0; }

        /* Overlay */
        .sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,.5); z-index: 999;
        }
        .sidebar-overlay.show { display: block; }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .topbar { left: 0 !important; }
            .main-content { margin-left: 0 !important; padding: 16px; }
            .form-row { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .stats-grid { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <i class="fa-solid fa-store"></i>
        <span>POS Jeruk Lokal</span>
    </div>
    <nav class="sidebar-menu">
        <div class="menu-label">Utama</div>
        <div class="menu-item">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge"></i> Dashboard
            </a>
        </div>
        <div class="menu-item">
            <a href="{{ route('pos.cashier') }}">
                <i class="fa-solid fa-cash-register"></i> Kasir POS
            </a>
        </div>

        <div class="menu-label">Produk</div>
        <div class="menu-item">
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fa-solid fa-tags"></i> Kategori
            </a>
        </div>
        <div class="menu-item">
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fa-solid fa-box"></i> Produk
            </a>
        </div>
        <div class="menu-item">
            <a href="{{ route('admin.bundles.index') }}" class="{{ request()->routeIs('admin.bundles.*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked"></i> Bundling
            </a>
        </div>

        <div class="menu-label">Transaksi</div>
        <div class="menu-item">
            <a href="{{ route('admin.transactions.index') }}" class="{{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Transaksi
            </a>
        </div>
        <div class="menu-item">
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users"></i> Pelanggan
            </a>
        </div>

        @if(auth()->user()->isAdmin())
        <div class="menu-label">Pengaturan</div>
        <div class="menu-item">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-gear"></i> Pengguna
            </a>
        </div>
        <div class="menu-item">
            <a href="{{ route('admin.settings.store.edit') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fa-solid fa-store"></i> Pengaturan Toko
            </a>
        </div>
        @endif
    </nav>
</aside>

<!-- Topbar -->
<header class="topbar" id="topbar">
    <div class="topbar-left">
        <button class="btn-toggle" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span style="font-weight:600;color:#333;">@yield('page-title', 'Dashboard')</span>
    </div>
    <div class="topbar-right">
        <div class="user-info">
            <strong>{{ auth()->user()->name }}</strong>
            <span>{{ ucfirst(auth()->user()->role) }}</span>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="d-none d-sm-inline">Keluar</span>
            </button>
        </form>
    </div>
</header>

<!-- Main -->
<main class="main-content" id="mainContent">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fa-solid fa-circle-xmark"></i> {{ session('error') }}
        </div>
    @endif

    @yield('content')
</main>

<script>
    let sidebarOpen = window.innerWidth > 768;

    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const main = document.getElementById('mainContent');
        const topbar = document.getElementById('topbar');

        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        } else {
            sidebarOpen = !sidebarOpen;
            sidebar.classList.toggle('hidden', !sidebarOpen);
            main.classList.toggle('full', !sidebarOpen);
            topbar.classList.toggle('full', !sidebarOpen);
        }
    }

    // Auto-hide sidebar on mobile
    if (window.innerWidth <= 768) {
        document.getElementById('sidebar').classList.remove('show');
    }
</script>
@stack('scripts')
</body>
</html>
