<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Assessment Pemetaan PKL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --primary-color: #4f46e5;
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --sidebar-bg: #0f172a;
            --sidebar-hover: rgba(255, 255, 255, 0.1);
            --content-bg: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--content-bg);
            color: var(--text-main);
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--sidebar-bg);
            color: #fff;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1040;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-logo {
            font-size: 1.25rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
            transition: opacity var(--transition-speed);
        }

        #sidebar.collapsed .brand-logo {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }

        .sidebar-content {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
            scrollbar-width: none; /* Firefox */
        }

        .sidebar-content::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }

        .nav-section-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: var(--text-muted);
            margin: 20px 10px 10px;
            font-weight: 700;
            transition: opacity var(--transition-speed);
        }

        #sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            position: relative;
        }

        .sidebar-nav-link i {
            font-size: 1.25rem;
            width: 24px;
            margin-right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: margin var(--transition-speed);
        }

        #sidebar.collapsed .sidebar-nav-link i {
            margin-right: 0;
        }

        .sidebar-nav-link span {
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            transition: opacity var(--transition-speed);
        }

        #sidebar.collapsed .sidebar-nav-link span {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }

        .sidebar-nav-link:hover {
            background: var(--sidebar-hover);
            color: #fff;
        }

        .sidebar-nav-link.active {
            background: var(--primary-gradient);
            color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .sidebar-nav-link.active i {
            color: #fff;
        }

        /* Sidebar Footer / User Info */
        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.2);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            transition: all var(--transition-speed);
        }

        #sidebar.collapsed .user-profile {
            justify-content: center;
            gap: 0;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .user-info {
            transition: opacity var(--transition-speed);
            overflow: hidden;
        }

        #sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
        }

        .user-name {
            font-size: 0.9rem;
            font-weight: 600;
            display: block;
            white-space: nowrap;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: block;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            width: 100%;
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            background: #ef4444;
            color: #fff;
        }

        #sidebar.collapsed .logout-btn span {
            display: none;
        }

        /* Main Content Styling */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        #main-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Top Header Area */
        .top-header {
            padding: 20px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        .toggle-sidebar {
            background: #fff;
            border: 1px solid #e2e8f0;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--text-main);
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .toggle-sidebar:hover {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        .page-header-title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .content-body {
            padding: 30px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            #sidebar {
                left: -var(--sidebar-width);
            }
            #sidebar.mobile-show {
                left: 0;
            }
            #main-wrapper {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 1035;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Custom Utilities for Premium Look */
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.05);
        }

        .btn-premium {
            background: var(--primary-gradient);
            border: none;
            color: #fff;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
            color: #fff;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <aside id="sidebar">
        <div class="sidebar-header">
            <div class="brand-logo">
                <i class="fas fa-shield-alt me-2"></i> ADMIN PANEL
            </div>
        </div>

        <div class="sidebar-content">
            <div class="nav-section-label">Main Menu</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.results') }}" class="sidebar-nav-link {{ Request::routeIs('admin.results') ? 'active' : '' }}">
                <i class="fas fa-chart-bar"></i>
                <span>Hasil Asesmen</span>
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('admin.questions') }}" class="sidebar-nav-link {{ Request::routeIs('admin.questions') ? 'active' : '' }}">
                <i class="fas fa-question-circle"></i>
                <span>Kelola Soal B1</span>
            </a>
            <a href="{{ route('admin.batch2ct.index') }}" class="sidebar-nav-link {{ Request::routeIs('admin.batch2ct.index') ? 'active' : '' }}">
                <i class="fas fa-brain"></i>
                <span>Kelola Soal B2</span>
            </a>
            <a href="{{ route('admin.students') }}" class="sidebar-nav-link {{ Request::routeIs('admin.students') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Kelola Siswa</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">
                    {{ substr(session('admin_name', 'A'), 0, 1) }}
                </div>
                <div class="user-info">
                    <span class="user-name">{{ session('admin_name', 'Administrator') }}</span>
                    <span class="user-role">Administrator</span>
                </div>
            </div>
            <a href="{{ route('admin.logout') }}" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <main id="main-wrapper">
        <header class="top-header">
            <div class="d-flex align-items-center">
                <button class="toggle-sidebar me-3" id="toggle-btn">
                    <i class="fas fa-bars"></i>
                </button>
                <h4 class="page-header-title">@yield('page_title', 'Dashboard')</h4>
            </div>
            
            <div class="top-header-actions">
                <!-- Additional header items can go here -->
            </div>
        </header>

        <div class="content-body">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const toggleBtn = $('#toggle-btn');
            const sidebar = $('#sidebar');
            const wrapper = $('#main-wrapper');
            const overlay = $('#sidebar-overlay');

            // Toggle Sidebar for Desktop
            toggleBtn.on('click', function() {
                if ($(window).width() > 992) {
                    sidebar.toggleClass('collapsed');
                    wrapper.toggleClass('expanded');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebar-collapsed', sidebar.hasClass('collapsed'));
                } else {
                    sidebar.toggleClass('mobile-show');
                    overlay.toggleClass('show');
                }
            });

            // Close sidebar on mobile when clicking overlay
            overlay.on('click', function() {
                sidebar.removeClass('mobile-show');
                overlay.removeClass('show');
            });

            // Restore state
            if (localStorage.getItem('sidebar-collapsed') === 'true' && $(window).width() > 992) {
                sidebar.addClass('collapsed');
                wrapper.addClass('expanded');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
