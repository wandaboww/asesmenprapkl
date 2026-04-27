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

        /* MOBILE FIRST SIDEBAR */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transform: translateX(-100%); /* Tertutup secara default menggunakan transform */
            background: var(--sidebar-bg);
            color: #fff;
            transition: transform var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1), width var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1040;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }

        #sidebar.mobile-show {
            transform: translateX(0); /* Terbuka saat diklik */
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

        .sidebar-header {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-logo {
            font-size: 1rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            white-space: nowrap;
            transition: opacity var(--transition-speed);
        }

        .btn-close-sidebar {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-close-sidebar:hover {
            background: rgba(239, 68, 68, 0.8);
        }

        .sidebar-content {
            flex: 1;
            padding: 20px 14px;
            overflow-y: auto;
            scrollbar-width: none;
        }

        .sidebar-content::-webkit-scrollbar {
            display: none;
        }

        .nav-section-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05rem;
            color: var(--text-muted);
            margin: 15px 5px 8px;
            font-weight: 700;
            transition: opacity var(--transition-speed);
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
            overflow: hidden;
        }

        .sidebar-nav-link i.main-icon {
            font-size: 1.25rem;
            width: 24px;
            margin-right: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: margin var(--transition-speed);
        }

        .sidebar-nav-link span {
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            transition: opacity var(--transition-speed);
            flex: 1;
        }

        .sidebar-nav-link i.toggle-icon {
            font-size: 0.75rem;
            transition: transform 0.3s;
        }

        .sidebar-nav-link[aria-expanded="true"] i.toggle-icon {
            transform: rotate(180deg);
        }

        .submenu-link {
            padding-left: 3.2rem; 
            font-size: 0.85rem;
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

        #main-wrapper {
            margin-left: 0;
            min-height: 100vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .top-header {
            padding: 15px 20px;
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
            font-size: 1.1rem;
            font-weight: 700;
        }

        .content-body {
            padding: 15px;
        }

        /* Desktop specific adjustments */
        @media (min-width: 992px) {
            #sidebar {
                transform: translateX(0); /* Visible by default on desktop */
            }
            
            #main-wrapper {
                margin-left: var(--sidebar-width);
            }
            
            .sidebar-overlay {
                display: none !important;
            }

            .top-header {
                padding: 20px 30px;
            }
            
            .content-body {
                padding: 30px;
            }
            
            .page-header-title {
                font-size: 1.25rem;
            }
            
            .brand-logo {
                font-size: 1.25rem;
            }

            .sidebar-header {
                padding: 24px;
            }

            /* COLLAPSED STATE STYLES (Desktop only) */
            #sidebar.collapsed {
                width: var(--sidebar-collapsed-width);
            }

            #main-wrapper.expanded {
                margin-left: var(--sidebar-collapsed-width);
            }

            #sidebar.collapsed .brand-logo {
                opacity: 0;
                width: 0;
                overflow: hidden;
            }

            #sidebar.collapsed .nav-section-label {
                opacity: 0;
                height: 0;
                margin: 0;
                overflow: hidden;
            }

            #sidebar.collapsed .sidebar-nav-link {
                justify-content: center;
                padding: 12px;
            }

            #sidebar.collapsed .sidebar-nav-link i.main-icon {
                margin-right: 0;
            }

            #sidebar.collapsed .sidebar-nav-link span {
                opacity: 0;
                width: 0;
                pointer-events: none;
                display: none;
            }

            #sidebar.collapsed .sidebar-nav-link i.toggle-icon {
                display: none;
            }
            
            #sidebar.collapsed .collapse {
                display: none !important;
            }

            #sidebar.collapsed .submenu-link {
                padding-left: 12px;
                justify-content: center;
            }

            #sidebar.collapsed .user-profile {
                justify-content: center;
                gap: 0;
            }

            #sidebar.collapsed .user-info {
                opacity: 0;
                width: 0;
                display: none;
            }

            #sidebar.collapsed .logout-btn {
                padding: 10px 0;
            }

            #sidebar.collapsed .logout-btn span {
                display: none;
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
            <!-- Tombol Close khusus untuk Mobile -->
            <button class="btn-close-sidebar d-lg-none" id="close-sidebar-mobile" aria-label="Close sidebar">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="sidebar-content">
            <div class="nav-section-label">Main Menu</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-nav-link {{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-th-large main-icon"></i>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.results') }}" class="sidebar-nav-link {{ Request::routeIs('admin.results') ? 'active' : '' }}">
                <i class="fas fa-chart-bar main-icon"></i>
                <span>Hasil Asesmen</span>
            </a>

            <div class="nav-section-label">Management</div>
            <a href="{{ route('admin.questions') }}" class="sidebar-nav-link {{ Request::routeIs('admin.questions') ? 'active' : '' }}">
                <i class="fas fa-question-circle main-icon"></i>
                <span>Kelola Soal B1</span>
            </a>
            <a href="#batch2ctMenu" data-bs-toggle="collapse" class="sidebar-nav-link {{ Request::routeIs('admin.batch2ct.*') ? 'active' : '' }}" aria-expanded="{{ Request::routeIs('admin.batch2ct.*') ? 'true' : 'false' }}">
                <i class="fas fa-brain main-icon"></i>
                <span>Kelola Soal B2</span>
                <i class="fas fa-chevron-down ms-auto toggle-icon"></i>
            </a>
            <div class="collapse {{ Request::routeIs('admin.batch2ct.*') ? 'show' : '' }}" id="batch2ctMenu">
                <a href="{{ route('admin.batch2ct.ringkasan') }}" class="sidebar-nav-link submenu-link {{ Request::routeIs('admin.batch2ct.ringkasan') ? 'active' : '' }}">
                    <i class="fas fa-chart-line main-icon"></i>
                    <span>Ringkasan</span>
                </a>
                <a href="{{ route('admin.batch2ct.ranking') }}" class="sidebar-nav-link submenu-link {{ Request::routeIs('admin.batch2ct.ranking') ? 'active' : '' }}">
                    <i class="fas fa-trophy main-icon"></i>
                    <span>Ranking Bidang</span>
                </a>
                <a href="{{ route('admin.batch2ct.ranking-lengkap') }}" class="sidebar-nav-link submenu-link {{ Request::routeIs('admin.batch2ct.ranking-lengkap') ? 'active' : '' }}">
                    <i class="fas fa-list-ol main-icon"></i>
                    <span>Ranking Lengkap</span>
                </a>
                <a href="{{ route('admin.batch2ct.kelola-soal') }}" class="sidebar-nav-link submenu-link {{ Request::routeIs('admin.batch2ct.kelola-soal') ? 'active' : '' }}">
                    <i class="fas fa-pen-to-square main-icon"></i>
                    <span>Kelola Soal</span>
                </a>
                <a href="{{ route('admin.batch2ct.bank-soal') }}" class="sidebar-nav-link submenu-link {{ Request::routeIs('admin.batch2ct.bank-soal') ? 'active' : '' }}">
                    <i class="fas fa-book main-icon"></i>
                    <span>Bank Soal</span>
                </a>
            </div>
            <a href="{{ route('admin.students') }}" class="sidebar-nav-link {{ Request::routeIs('admin.students') ? 'active' : '' }}">
                <i class="fas fa-users main-icon"></i>
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
            const closeBtnMobile = $('#close-sidebar-mobile');
            const sidebar = $('#sidebar');
            const wrapper = $('#main-wrapper');
            const overlay = $('#sidebar-overlay');

            // Toggle Sidebar
            toggleBtn.on('click', function() {
                if ($(window).width() >= 992) {
                    // Desktop
                    sidebar.toggleClass('collapsed');
                    wrapper.toggleClass('expanded');
                    
                    // Save state to localStorage
                    localStorage.setItem('sidebar-collapsed', sidebar.hasClass('collapsed'));
                    
                    // Collapse submenus properly to avoid logic conflicts if re-expanded
                    if (sidebar.hasClass('collapsed')) {
                        $('.collapse.show').collapse('hide');
                    }
                } else {
                    // Mobile
                    sidebar.toggleClass('mobile-show');
                    overlay.toggleClass('show');
                }
            });

            // Close sidebar on mobile with the specific close button
            closeBtnMobile.on('click', function() {
                sidebar.removeClass('mobile-show');
                overlay.removeClass('show');
            });

            // Close sidebar on mobile when clicking overlay
            overlay.on('click', function() {
                sidebar.removeClass('mobile-show');
                overlay.removeClass('show');
            });

            // Restore state on Desktop
            if ($(window).width() >= 992 && localStorage.getItem('sidebar-collapsed') === 'true') {
                sidebar.addClass('collapsed');
                wrapper.addClass('expanded');
                // Ensure submenus are closed if restored as collapsed
                $('.collapse.show').removeClass('show');
            }
        });
    </script>
    @yield('scripts')
</body>
</html>