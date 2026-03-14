<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Priority Bank') }} - @yield('title')</title>
    
    <!-- Favicons -->
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
    
    <!-- Web App Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <!-- Compiled Assets -->
    @php
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? 'assets/app.css';
        $jsFile = $manifest['resources/js/app.js']['file'] ?? 'assets/app.js';
    @endphp
    <link rel="stylesheet" href="{{ asset('build/' . $cssFile) }}">
    @unless(request()->routeIs('incomes.index') || request()->routeIs('expenses.index'))
    <script src="{{ asset('build/' . $jsFile) }}" defer></script>
    @endunless
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --secondary: #10b981;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1a202c;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --border-radius: 0.75rem;
            --border-radius-lg: 1rem;
            --shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --sidebar-width: 280px;
            --header-height: 70px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 100vh;
            color: var(--dark);
            overflow-x: hidden;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--primary-dark); }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
            backdrop-filter: blur(20px);
            box-shadow: 4px 0 30px rgba(0, 0, 0, 0.08);
            z-index: 1000;
            transition: var(--transition);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(79, 70, 229, 0.1);
            display: flex;
            align-items: center;
            gap: 15px;
            flex-shrink: 0;
        }

        .sidebar-logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
            overflow: hidden;
        }

        .sidebar-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
        }

        .sidebar-brand {
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .sidebar-menu {
            padding: 1.5rem 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-menu-item {
            margin: 0.5rem 1rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: var(--gray-700);
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition);
            font-weight: 600;
            position: relative;
        }

        .sidebar-link i {
            width: 24px;
            text-align: center;
            font-size: 18px;
            color: var(--gray-500);
            transition: var(--transition);
        }

        .sidebar-link:hover {
            background: rgba(79, 70, 229, 0.08);
            color: var(--primary);
            transform: translateX(5px);
        }

        .sidebar-link:hover i {
            color: var(--primary);
        }

        .sidebar-link.active {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(79, 70, 229, 0.1) 100%);
            color: var(--primary);
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.15);
        }

        .sidebar-link.active i {
            color: var(--primary);
        }

        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: linear-gradient(180deg, var(--primary), var(--primary-dark));
            border-radius: 0 4px 4px 0;
        }

        /* Collapsible sidebar group */
        .sidebar-group { margin: 0.5rem 1rem; }
        .sidebar-group-header {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 20px;
            color: var(--gray-700);
            border-radius: 12px;
            cursor: pointer;
            transition: var(--transition);
            font-weight: 600;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }
        .sidebar-group-header i { width: 24px; text-align: center; font-size: 18px; color: var(--gray-500); transition: var(--transition); }
        .sidebar-group-header:hover { background: rgba(79, 70, 229, 0.08); color: var(--primary); }
        .sidebar-group-header:hover i { color: var(--primary); }
        .sidebar-group-header .sidebar-group-chevron { margin-left: auto; font-size: 12px; color: var(--gray-500); transition: transform 0.2s ease; }
        .sidebar-group.open .sidebar-group-chevron { transform: rotate(180deg); }
        .sidebar-group-content { overflow: hidden; }
        .sidebar-group-content.collapsed { display: none; }
        .sidebar-submenu-item { margin: 0 0 0 1.5rem; }
        .sidebar-submenu-item .sidebar-link { padding: 10px 16px; font-size: 0.9rem; }

        /* New Transaction Modal: source radio cards */
        .source-radio-card { -webkit-tap-highlight-color: transparent; }
        .source-radio-card:hover { background: #f3f4f6 !important; border-color: #d1d5db !important; }
        .source-radio-card.source-radio-card-selected { background: rgba(79, 70, 229, 0.08) !important; border-color: var(--primary) !important; box-shadow: 0 0 0 1px var(--primary); }
        .source-radio-card.source-radio-card-selected .source-radio-card-icon { background: rgba(79, 70, 229, 0.2) !important; color: var(--primary) !important; }
        .source-radio:focus + .source-radio-card-icon,
        .source-radio:focus ~ .source-radio-card-icon { outline: 2px solid var(--primary); outline-offset: 2px; }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(79, 70, 229, 0.1);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.95) 100%);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            border-radius: 12px;
            background: rgba(79, 70, 229, 0.05);
        }

        .sidebar-user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .sidebar-user-info {
            flex: 1;
        }

        .sidebar-user-name {
            font-weight: 700;
            color: var(--dark);
            font-size: 14px;
        }

        .sidebar-user-role {
            font-size: 12px;
            color: var(--gray-500);
        }

        /* Main Content */
        .main-container {
            margin-left: var(--sidebar-width);
            padding: 2rem 3rem;
            min-height: 100vh;
            transition: var(--transition);
        }

        .mobile-menu-toggle {
            display: none;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                box-shadow: 4px 0 30px rgba(0, 0, 0, 0.15);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-container {
                margin-left: 0;
                padding: 1rem;
            }

            .mobile-menu-toggle {
                position: fixed;
                top: 20px;
                left: 20px;
                z-index: 1001;
                width: 50px;
                height: 50px;
                background: white;
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                cursor: pointer;
                transition: var(--transition);
            }

            .mobile-menu-toggle:hover {
                transform: scale(1.1);
            }

            .mobile-menu-toggle i {
                font-size: 20px;
                color: var(--primary);
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Cards */
        .bank-card {
            background: white;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
            border: 1px solid rgba(0,0,0,0.05);
            transition: var(--transition);
            overflow: hidden;
        }

        .bank-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        }

        .bank-card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 1.5rem 2rem;
            border-bottom: none;
        }

        .bank-card-header h3 {
            font-weight: 700;
            margin: 0;
            font-size: 1.3rem;
        }

        .bank-card-body {
            padding: 2rem;
        }

        /* Stats Cards */
        .stat-card {
            padding: 2rem;
            border-radius: var(--border-radius-lg);
            background: white;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
        }

        .stat-icon {
            width: 70px;
            height: 70px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            line-height: 1;
        }

        .stat-label {
            color: var(--gray-600);
            font-size: 1rem;
            margin-top: 0.5rem;
            font-weight: 600;
        }

        /* Buttons */
        .btn-bank {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            color: white;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .btn-bank:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
        }

        /* Page Header */
        .page-header {
            margin-bottom: 2.5rem;
            position: relative;
        }

        .page-title {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
            position: relative;
            display: inline-block;
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--primary-dark));
            border-radius: 2px;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: var(--gray-600);
            font-weight: 500;
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Top Navigation Bar */
        .top-nav-bar {
            transition: var(--transition);
        }

        @media (max-width: 992px) {
            .top-nav-bar {
                left: 0 !important;
            }
        }

        /* Transaction Modal */
        .transaction-modal {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header button:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        /* Notification Toast */
        .notification-toast {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .top-nav-bar {
                padding: 0 1rem !important;
            }

            .top-nav-bar h2 {
                font-size: 1rem !important;
            }

            .modal-content {
                width: 95% !important;
                max-height: 95vh !important;
            }

            .notification-toast {
                right: 1rem !important;
                left: 1rem !important;
                min-width: auto !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Logo">
            </div>
            <div class="sidebar-brand">{{ config('app.name', 'Priority Bank') }}</div>
        </div>

        <nav class="sidebar-menu">
            @if(auth()->user()->isAdmin())
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Admin Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('transactions.index') }}" class="sidebar-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>User Management</span>
                    </a>
                </div>
                @php
                    $routeUser = request()->routeIs('admin.users.show') ? request()->routeParameter('user') : null;
                    $currentUserId = $routeUser ? (is_object($routeUser) ? $routeUser->id : $routeUser) : null;
                    $subaccountsActive = request()->routeIs('admin.fund-sources.*') || ($currentUserId && ($sidebarSubaccounts ?? collect())->contains('user_id', $currentUserId));
                @endphp
                <div class="sidebar-group {{ $subaccountsActive ? 'open' : '' }}" id="sidebarSubaccountsGroup">
                    <button type="button" class="sidebar-group-header" aria-expanded="{{ $subaccountsActive ? 'true' : 'false' }}" aria-controls="sidebarSubaccountsContent" id="sidebarSubaccountsToggle">
                        <i class="fas fa-coins"></i>
                        <span>SubAccounts</span>
                        <i class="fas fa-chevron-down sidebar-group-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="sidebar-group-content {{ $subaccountsActive ? '' : 'collapsed' }}" id="sidebarSubaccountsContent">
                        <div class="sidebar-submenu-item">
                            <a href="{{ route('admin.fund-sources.index') }}" class="sidebar-link {{ request()->routeIs('admin.fund-sources.*') ? 'active' : '' }}">
                                <i class="fas fa-th-list"></i>
                                <span>All SubAccounts</span>
                            </a>
                        </div>
                        @foreach($sidebarSubaccounts ?? [] as $source)
                            <div class="sidebar-submenu-item">
                                @if($source->user_id && $source->user)
                                    <a href="{{ route('admin.users.show', $source->user) }}" class="sidebar-link {{ $currentUserId === $source->user_id ? 'active' : '' }}">
                                        <i class="fas fa-user-circle"></i>
                                        <span>{{ $source->name }}</span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.fund-sources.index') }}" class="sidebar-link">
                                        <i class="fas fa-coins"></i>
                                        <span>{{ $source->name }}</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('savings.index', ['approval' => 'pending']) }}" class="sidebar-link {{ request()->routeIs('savings.*') && request()->get('approval') === 'pending' ? 'active' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        <span>Pre-approval</span>
                    </a>
                </div>
            @else
                <div class="sidebar-menu-item">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
            @endif
            
            @if(!auth()->user()->isAdmin())
                <div class="sidebar-menu-item">
                    <a href="{{ route('savings.index') }}" class="sidebar-link {{ request()->routeIs('savings.*') ? 'active' : '' }}">
                        <i class="fas fa-piggy-bank"></i>
                        <span>Savings</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('loans.index') }}" class="sidebar-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Loans</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('transactions.index') }}" class="sidebar-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                    </a>
                </div>
            @else
                <div class="sidebar-menu-item">
                    <a href="{{ route('loans.index') }}" class="sidebar-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                        <i class="fas fa-university"></i>
                        <span>Priority Bank</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('budgets.index') }}" class="sidebar-link {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
                        <i class="fas fa-pie-chart"></i>
                        <span>Budgets</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('incomes.index') }}" class="sidebar-link {{ request()->routeIs('incomes.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-down"></i>
                        <span>Income</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                        <i class="fas fa-arrow-up"></i>
                        <span>Expenses</span>
                    </a>
                </div>
            @endif
            
            @if(auth()->user()->isAdmin())
                <div class="sidebar-menu-item">
                    <a href="{{ route('api-keys.index') }}" class="sidebar-link {{ request()->routeIs('api-keys.*') ? 'active' : '' }}">
                        <i class="fas fa-key"></i>
                        <span>Source Keys</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.ai-insights.index') }}" class="sidebar-link {{ request()->routeIs('admin.ai-insights.*') ? 'active' : '' }}">
                        <i class="fas fa-chart-line"></i>
                        <span>Insights & Recommendations</span>
                    </a>
                </div>
                @php
                    $settingsLogsActive = request()->routeIs('admin.payment-settings.*') || request()->routeIs('admin.notification-settings.*') || request()->routeIs('admin.logs.*') || request()->routeIs('admin.queue-jobs.*');
                @endphp
                <div class="sidebar-group {{ $settingsLogsActive ? 'open' : '' }}" id="sidebarSettingsLogsGroup">
                    <button type="button" class="sidebar-group-header" aria-expanded="{{ $settingsLogsActive ? 'true' : 'false' }}" aria-controls="sidebarSettingsLogsContent" id="sidebarSettingsLogsToggle">
                        <i class="fas fa-cog"></i>
                        <span>Settings & Logs</span>
                        <i class="fas fa-chevron-down sidebar-group-chevron" aria-hidden="true"></i>
                    </button>
                    <div class="sidebar-group-content {{ $settingsLogsActive ? '' : 'collapsed' }}" id="sidebarSettingsLogsContent">
                        <div class="sidebar-submenu-item">
                            <a href="{{ route('admin.payment-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">
                                <i class="fas fa-credit-card"></i>
                                <span>Payment Settings</span>
                            </a>
                        </div>
                        <div class="sidebar-submenu-item">
                            <a href="{{ route('admin.notification-settings.index') }}" class="sidebar-link {{ request()->routeIs('admin.notification-settings.*') ? 'active' : '' }}">
                                <i class="fas fa-bell"></i>
                                <span>Notification Settings</span>
                            </a>
                        </div>
                        <div class="sidebar-submenu-item">
                            <a href="{{ route('admin.logs.index') }}" class="sidebar-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                                <i class="fas fa-file-alt"></i>
                                <span>System Logs</span>
                            </a>
                        </div>
                        <div class="sidebar-submenu-item">
                            <a href="{{ route('admin.queue-jobs.index') }}" class="sidebar-link {{ request()->routeIs('admin.queue-jobs.*') ? 'active' : '' }}">
                                <i class="fas fa-list-alt"></i>
                                <span>Queued Jobs</span>
                                @if(($pendingJobsCount ?? 0) > 0 || ($failedJobsCount ?? 0) > 0)
                                    <span class="ml-2 px-1.5 py-0.5 text-xs rounded {{ ($failedJobsCount ?? 0) > 0 ? 'bg-red-500' : 'bg-amber-500' }} text-white">{{ ($pendingJobsCount ?? 0) + ($failedJobsCount ?? 0) }}</span>
                                @endif
                            </a>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="sidebar-menu-item" style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(79, 70, 229, 0.1);">
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile Settings</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ auth()->user()->isAdmin() ? 'Administrator' : 'Member' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mb-0">
                    @csrf
                    <button type="submit" class="btn btn-link p-0" style="color: var(--gray-500);">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Navigation Bar (Admin Only) -->
    @if(auth()->user()->isAdmin())
    <div class="top-nav-bar" style="position: fixed; top: 0; left: var(--sidebar-width); right: 0; height: 70px; background: white; border-bottom: 1px solid rgba(0,0,0,0.1); z-index: 999; display: flex; align-items: center; justify-content: space-between; padding: 0 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <div class="flex items-center">
            <h2 class="text-xl font-bold" style="color: var(--dark); margin: 0;">Admin Panel</h2>
        </div>
        <div class="flex items-center gap-4">
            <button id="newTransactionBtn" class="new-transaction-btn" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%); border: none; color: white; font-size: 24px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3); transition: var(--transition);" title="New Transaction">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="main-container animate-in" style="@if(auth()->user()->isAdmin()) margin-top: 70px; @endif">
        @if(session()->has('impersonating'))
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" style="margin: 1rem 0;">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-user-secret mr-2"></i>
                        <span class="font-semibold">You are currently impersonating {{ auth()->user()->name }} ({{ auth()->user()->email }})</span>
                    </div>
                    <form action="{{ route('admin.users.stop-impersonating') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-blue-600 px-4 py-2 rounded text-sm font-medium font-semibold">
                            Stop Impersonating
                        </button>
                    </form>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--border-radius); padding: 1rem 1.5rem; color: var(--success);">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- New Transaction Modal -->
    @if(auth()->user()->isAdmin())
    <div id="transactionModal" class="transaction-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
        <div class="modal-content" style="background: white; border-radius: var(--border-radius-lg); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 1px solid rgba(0,0,0,0.1); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 700; color: var(--dark);">New Transaction</h3>
                <button id="closeModalBtn" style="background: none; border: none; font-size: 24px; color: var(--gray-500); cursor: pointer; padding: 0; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: var(--transition);">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" style="padding: 2rem;">
                <form id="transactionForm">
                    @csrf
                    <!-- Source Selection (First) - Personal or Bank as card-style radios -->
                    @php
                        $protectedSources = \App\Models\SystemRegistry::where('is_protected', true)->where('active_status', true)->orderBy('name')->get();
                        $personalCeoSource = $protectedSources->firstWhere('system_id', 'personal_ceo');
                        $priorityBankSource = $protectedSources->firstWhere('system_id', 'priority_bank');
                        $users = \App\Models\User::where('status', 'approved')->orderBy('name')->pluck('name', 'id');
                    @endphp
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Source</label>
                        <div class="flex gap-3 flex-wrap">
                            <label class="source-radio-card flex-1 min-w-[120px] cursor-pointer rounded-xl border-2 p-4 transition-all duration-200 flex items-center gap-3"
                                style="background: #f9fafb; border-color: #e5e7eb;">
                                <input type="radio" name="external_system_id" value="{{ $personalCeoSource->id ?? '' }}"
                                    data-system-id="personal_ceo" class="sr-only source-radio">
                                <span class="source-radio-card-icon flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(107, 114, 128, 0.2); color: #4b5563;">
                                    <i class="fas fa-user"></i>
                                </span>
                                <span class="font-semibold text-gray-700">Personal</span>
                            </label>
                            <label class="source-radio-card flex-1 min-w-[120px] cursor-pointer rounded-xl border-2 p-4 transition-all duration-200 flex items-center gap-3 source-radio-card-selected"
                                style="background: rgba(79, 70, 229, 0.08); border-color: var(--primary);">
                                <input type="radio" name="external_system_id" value="{{ $priorityBankSource->id ?? '' }}"
                                    data-system-id="priority_bank" class="sr-only source-radio" checked>
                                <span class="source-radio-card-icon flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center" style="background: rgba(79, 70, 229, 0.2); color: var(--primary);">
                                    <i class="fas fa-university"></i>
                                </span>
                                <span class="font-semibold text-gray-700">Bank</span>
                            </label>
                        </div>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_external_system_id" style="display: none;"></span>
                    </div>

                    <!-- User Selection (Only for Priority Bank) -->
                    <div class="mb-4" id="userSelectField" style="display: none;">
                        <label for="modal_user_id" class="block text-sm font-medium text-gray-700 mb-2">@ User</label>
                        <select name="user_id" id="modal_user_id"
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Search or select user...</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_user_id" style="display: none;"></span>
                    </div>

                    <!-- Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Transaction Type *</label>
                        <div class="flex gap-6 mt-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="type" id="modal_type_expense" value="expense" checked required
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700" id="expense_label">Expense</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="type" id="modal_type_income" value="income" required
                                    class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500">
                                <span class="ml-2 text-sm font-medium text-gray-700" id="income_label">Income</span>
                            </label>
                        </div>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_type" style="display: none;"></span>
                    </div>

                    <!-- Dynamic Category Selection -->
                    <div class="mb-4" id="categoryField">
                        <label for="modal_category" class="block text-sm font-medium text-gray-700 mb-2">Category/Directorate *</label>
                        <select name="category" id="modal_category" required
                            class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select Category/Directorate</option>
                        </select>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_category" style="display: none;"></span>
                    </div>
                    
                    <!-- Priority Bank Category Display (hidden by default) -->
                    <div class="mb-4" id="priorityBankCategory" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category/Directorate *</label>
                        <div class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 bg-gray-100 sm:text-sm rounded-md">
                            <span id="priorityBankCategoryValue" class="text-gray-700 font-medium">-</span>
                        </div>
                        <input type="hidden" name="category" id="priorityBankCategoryInput" value="">
                    </div>

                    <!-- Amount -->
                    <div class="mb-4">
                        <label for="modal_amount" class="block text-sm font-medium text-gray-700 mb-2">Amount (GHS) *</label>
                        <input type="number" step="0.01" name="amount" id="modal_amount" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2 px-3">
                        <span class="error-message text-red-600 text-sm mt-1" id="error_amount" style="display: none;"></span>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="modal_description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="modal_description" rows="3"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2 px-3"></textarea>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_description" style="display: none;"></span>
                    </div>

                    <!-- Notes (pre-approval / internal) -->
                    <div class="mb-4">
                        <label for="modal_notes" class="block text-sm font-medium text-gray-700 mb-2">Note (pre-approval / internal)</label>
                        <textarea name="notes" id="modal_notes" rows="2"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2 px-3"
                            placeholder="Optional note visible in transaction details"></textarea>
                        <span class="error-message text-red-600 text-sm mt-1" id="error_notes" style="display: none;"></span>
                    </div>

                    <!-- Date (Last) -->
                    <div class="mb-4">
                        <label for="modal_date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                        <input type="date" name="date" id="modal_date" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm py-2 px-3"
                            value="{{ now()->format('Y-m-d') }}">
                        <span class="error-message text-red-600 text-sm mt-1" id="error_date" style="display: none;"></span>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" id="cancelBtn" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50" style="transition: var(--transition);">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md" style="transition: var(--transition);">
                            <span id="submitBtnText">Save Transaction</span>
                            <span id="submitBtnLoader" style="display: none;"><i class="fas fa-spinner fa-spin"></i> Saving...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Toast -->
    <div id="notificationToast" class="notification-toast" style="position: fixed; top: 90px; right: 2rem; background: white; border-radius: var(--border-radius); padding: 1rem 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 10001; display: none; min-width: 300px; border-left: 4px solid var(--success);">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle" style="color: var(--success); font-size: 20px;"></i>
            <div>
                <p style="margin: 0; font-weight: 600; color: var(--dark);" id="notificationMessage">Transaction saved successfully!</p>
            </div>
            <button id="closeNotificationBtn" style="background: none; border: none; color: var(--gray-500); cursor: pointer; margin-left: auto;">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // Mobile menu toggle
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (mobileMenuToggle && sidebar) {
            mobileMenuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                sidebar.classList.toggle('show');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.toggle('show');
                }
            });
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth < 992) {
                if (sidebar && !sidebar.contains(event.target) && 
                    mobileMenuToggle && !mobileMenuToggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                    if (sidebarOverlay) sidebarOverlay.classList.remove('show');
                }
            }
        });

        // Close sidebar when clicking on links on mobile
        document.querySelectorAll('.sidebar-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    sidebar.classList.remove('show');
                    const overlay = document.getElementById('sidebarOverlay');
                    if (overlay) overlay.classList.remove('show');
                }
            });
        });

        // SubAccounts collapsible
        (function() {
            const toggle = document.getElementById('sidebarSubaccountsToggle');
            const group = document.getElementById('sidebarSubaccountsGroup');
            const content = document.getElementById('sidebarSubaccountsContent');
            if (toggle && group && content) {
                toggle.addEventListener('click', function() {
                    group.classList.toggle('open');
                    content.classList.toggle('collapsed');
                    toggle.setAttribute('aria-expanded', content.classList.contains('collapsed') ? 'false' : 'true');
                });
            }
        })();

        // Settings & Logs collapsible
        (function() {
            const toggle = document.getElementById('sidebarSettingsLogsToggle');
            const group = document.getElementById('sidebarSettingsLogsGroup');
            const content = document.getElementById('sidebarSettingsLogsContent');
            if (toggle && group && content) {
                toggle.addEventListener('click', function() {
                    group.classList.toggle('open');
                    content.classList.toggle('collapsed');
                    toggle.setAttribute('aria-expanded', content.classList.contains('collapsed') ? 'false' : 'true');
                });
            }
        })();

        // Transaction Modal and AJAX Submission
        @if(auth()->user()->isAdmin())
        (function() {
            const modal = document.getElementById('transactionModal');
            const newTransactionBtn = document.getElementById('newTransactionBtn');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const transactionForm = document.getElementById('transactionForm');
            const notificationToast = document.getElementById('notificationToast');
            const closeNotificationBtn = document.getElementById('closeNotificationBtn');
            
            // Fetch categories from database (ensure we always have income/expense arrays)
            const _raw = @json(\App\Models\Category::all()->groupBy(function($category) {
                if ($category->type === 'both') {
                    return 'both';
                }
                return $category->type;
            })->map(function($group) {
                return $group->pluck('name')->toArray();
            })->toArray());
            const categories = (typeof _raw === 'object' && _raw !== null) ? _raw : {};
            if (!Array.isArray(categories.income)) categories.income = [];
            if (!Array.isArray(categories.expense)) categories.expense = [];
            if (!Array.isArray(categories.both)) categories.both = [];
            if (categories.both.length) {
                categories.income = [...categories.income, ...categories.both];
                categories.expense = [...categories.expense, ...categories.both];
            }

            // Open modal
            if (newTransactionBtn) {
                newTransactionBtn.addEventListener('click', function() {
                    if (modal) {
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                        setTimeout(function() {
                            if (typeof syncSourceCardStyles === 'function') syncSourceCardStyles();
                            var firstRadio = document.querySelector('input[name="external_system_id"]:checked');
                            if (firstRadio) firstRadio.focus();
                        }, 100);
                    }
                });
            }

            // Close modal functions
            function closeModal() {
                if (modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                    transactionForm.reset();
                    // Clear error messages
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.style.display = 'none';
                        el.textContent = '';
                    });
                    // Reset category select
                    const categorySelect = document.getElementById('modal_category');
                    if (categorySelect) {
                        categorySelect.innerHTML = '<option value="">Select Category</option>';
                        categorySelect.setAttribute('name', 'category'); // Restore name attribute
                        categorySelect.setAttribute('required', 'required');
                    }
                    // Reset Priority Bank category
                    const priorityBankCategory = document.getElementById('priorityBankCategory');
                    const priorityBankCategoryInput = document.getElementById('priorityBankCategoryInput');
                    if (priorityBankCategory) priorityBankCategory.style.display = 'none';
                    if (priorityBankCategoryInput) priorityBankCategoryInput.value = '';
                    // Reset category field visibility
                    const categoryField = document.getElementById('categoryField');
                    if (categoryField) categoryField.style.display = 'block';
                    // Reset user field
                    const userSelectField = document.getElementById('userSelectField');
                    if (userSelectField) {
                        userSelectField.style.display = 'none';
                    }
                    const userSelect = document.getElementById('modal_user_id');
                    if (userSelect) {
                        userSelect.removeAttribute('required');
                        // Destroy Select2 if initialized (guard against missing jQuery)
                        if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.select2 === 'function' && userSelect.classList && userSelect.classList.contains('select2-hidden-accessible')) {
                            $(userSelect).select2('destroy');
                        }
                        userSelect.value = '';
                    }
                    // Reset labels
                    const expenseLabel = document.querySelector('label[for="type_expense"]');
                    const incomeLabel = document.querySelector('label[for="type_income"]');
                    if (expenseLabel) expenseLabel.textContent = 'Expense';
                    if (incomeLabel) incomeLabel.textContent = 'Income';
                    // Also reset labels by ID if they exist
                    const expenseLabelById = document.getElementById('expense_label');
                    const incomeLabelById = document.getElementById('income_label');
                    if (expenseLabelById) expenseLabelById.textContent = 'Expense';
                    if (incomeLabelById) incomeLabelById.textContent = 'Income';
                }
            }

            if (closeModalBtn) {
                closeModalBtn.addEventListener('click', closeModal);
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', closeModal);
            }

            // Close modal when clicking outside
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            // Handle source change (Personal vs Bank radio cards) to show/hide user field
            const sourceRadios = document.querySelectorAll('input[name="external_system_id"].source-radio');
            const sourceCards = document.querySelectorAll('.source-radio-card');
            const userSelectField = document.getElementById('userSelectField');
            const userSelect = document.getElementById('modal_user_id');
            
            function getSelectedSourceRadio() {
                return document.querySelector('input[name="external_system_id"]:checked');
            }
            
            function getSourceSystemId() {
                const radio = getSelectedSourceRadio();
                return radio ? radio.getAttribute('data-system-id') : null;
            }
            
            function syncSourceCardStyles() {
                if (!sourceCards || !sourceCards.length) return;
                sourceCards.forEach(function(card) {
                    card.classList.remove('source-radio-card-selected');
                    card.style.background = '#f9fafb';
                    card.style.borderColor = '#e5e7eb';
                    var icon = card.querySelector('.source-radio-card-icon');
                    if (icon) { icon.style.background = 'rgba(107, 114, 128, 0.2)'; icon.style.color = '#4b5563'; }
                });
                var sel = getSelectedSourceRadio();
                if (sel) {
                    var card = sel.closest('.source-radio-card');
                    if (card) {
                        card.classList.add('source-radio-card-selected');
                        card.style.background = 'rgba(79, 70, 229, 0.08)';
                        card.style.borderColor = 'var(--primary)';
                        var icon = card.querySelector('.source-radio-card-icon');
                        if (icon) { icon.style.background = 'rgba(79, 70, 229, 0.2)'; icon.style.color = 'var(--primary)'; }
                    }
                }
            }
            
            function toggleUserField() {
                const selectedRadio = getSelectedSourceRadio();
                const systemId = selectedRadio ? selectedRadio.getAttribute('data-system-id') : null;
                if (userSelectField) {
                    const expenseLabel = document.getElementById('expense_label');
                    const incomeLabel = document.getElementById('income_label');
                    const categoryField = document.getElementById('categoryField');
                    const priorityBankCategory = document.getElementById('priorityBankCategory');
                    const priorityBankCategoryValue = document.getElementById('priorityBankCategoryValue');
                    const priorityBankCategoryInput = document.getElementById('priorityBankCategoryInput');
                    const categorySelect = document.getElementById('modal_category');
                    
                    if (systemId === 'priority_bank') {
                        userSelectField.style.display = 'block';
                        if (userSelect) {
                            userSelect.setAttribute('required', 'required');
                            // Initialize Select2 if not already initialized (guard against missing jQuery)
                            var hasSelect2 = userSelect.classList && userSelect.classList.contains('select2-hidden-accessible');
                            if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.select2 === 'function' && !hasSelect2) {
                                $(userSelect).select2({
                                    theme: 'bootstrap-5',
                                    placeholder: 'Select User',
                                    allowClear: true,
                                    width: '100%',
                                    dropdownParent: modal
                                });
                            }
                        }
                        // Update labels for Priority Bank
                        if (expenseLabel) expenseLabel.textContent = 'Expense(Loan)';
                        if (incomeLabel) incomeLabel.textContent = 'Income(Savings)';
                        
                        // Hide regular category field and show Priority Bank category
                        if (categoryField) categoryField.style.display = 'none';
                        if (categorySelect) {
                            categorySelect.removeAttribute('required');
                            categorySelect.removeAttribute('name'); // Remove name to prevent submission
                        }
                        if (priorityBankCategory) priorityBankCategory.style.display = 'block';
                        if (priorityBankCategoryInput) {
                            priorityBankCategoryInput.setAttribute('name', 'category'); // Ensure name is set for Priority Bank
                        }
                        
                        // Update Priority Bank category based on selected type
                        updatePriorityBankCategory();
                    } else {
                        userSelectField.style.display = 'none';
                        if (userSelect) {
                            userSelect.removeAttribute('required');
                            // Destroy Select2 if initialized (guard against missing jQuery)
                            if (typeof $ !== 'undefined' && typeof $.fn !== 'undefined' && typeof $.fn.select2 === 'function' && userSelect.classList && userSelect.classList.contains('select2-hidden-accessible')) {
                                $(userSelect).select2('destroy');
                            }
                            userSelect.value = '';
                        }
                        // Reset labels to default
                        if (expenseLabel) expenseLabel.textContent = 'Expense';
                        if (incomeLabel) incomeLabel.textContent = 'Income';
                        
                        // Show regular category field and hide Priority Bank category
                        if (categoryField) categoryField.style.display = 'block';
                        if (categorySelect) {
                            categorySelect.setAttribute('required', 'required');
                            categorySelect.setAttribute('name', 'category'); // Restore name attribute
                        }
                        if (priorityBankCategory) priorityBankCategory.style.display = 'none';
                        if (priorityBankCategoryInput) {
                            priorityBankCategoryInput.value = '';
                            priorityBankCategoryInput.removeAttribute('name'); // Remove name to prevent conflicts
                        }
                    }
                }
            }
            
            // Update Priority Bank category based on selected transaction type
            function updatePriorityBankCategory() {
                const selectedType = document.querySelector('input[name="type"]:checked')?.value;
                const priorityBankCategoryValue = document.getElementById('priorityBankCategoryValue');
                const priorityBankCategoryInput = document.getElementById('priorityBankCategoryInput');
                
                if (selectedType === 'expense') {
                    if (priorityBankCategoryValue) priorityBankCategoryValue.textContent = 'Loan';
                    if (priorityBankCategoryInput) priorityBankCategoryInput.value = 'Loan';
                } else if (selectedType === 'income') {
                    if (priorityBankCategoryValue) priorityBankCategoryValue.textContent = 'Savings';
                    if (priorityBankCategoryInput) priorityBankCategoryInput.value = 'Savings';
                } else {
                    // Default to Loan if no type selected (expense is default)
                    if (priorityBankCategoryValue) priorityBankCategoryValue.textContent = 'Loan';
                    if (priorityBankCategoryInput) priorityBankCategoryInput.value = 'Loan';
                }
            }
            
            // Source radio cards: update selected card style and toggle user field
            if (sourceRadios && sourceRadios.length) {
                sourceRadios.forEach(function(radio) {
                    radio.addEventListener('change', function() {
                        sourceCards.forEach(function(card) {
                            card.classList.remove('source-radio-card-selected');
                            card.style.background = '#f9fafb';
                            card.style.borderColor = '#e5e7eb';
                            var icon = card.querySelector('.source-radio-card-icon');
                            if (icon) { icon.style.background = 'rgba(107, 114, 128, 0.2)'; icon.style.color = '#4b5563'; }
                        });
                        var card = radio.closest('.source-radio-card');
                        if (card) {
                            card.classList.add('source-radio-card-selected');
                            card.style.background = 'rgba(79, 70, 229, 0.08)';
                            card.style.borderColor = 'var(--primary)';
                            var icon = card.querySelector('.source-radio-card-icon');
                            if (icon) { icon.style.background = 'rgba(79, 70, 229, 0.2)'; icon.style.color = 'var(--primary)'; }
                        }
                        toggleUserField();
                        if (getSourceSystemId() !== 'priority_bank') updateCategories();
                    });
                });
                setTimeout(function() {
                    toggleUserField();
                    if (getSourceSystemId() === 'priority_bank' && userSelect && typeof $ !== 'undefined' && $.fn.select2) {
                        if (!userSelect.classList.contains('select2-hidden-accessible')) {
                            $(userSelect).select2({
                                theme: 'bootstrap-5',
                                placeholder: 'Search or select user...',
                                allowClear: true,
                                width: '100%',
                                dropdownParent: modal
                            });
                        }
                    }
                }, 100);
            }

            // Handle type change to update categories
            const typeRadios = document.querySelectorAll('input[name="type"]');
            const categorySelect = document.getElementById('modal_category');
            
            function updateCategories() {
                const selectedType = document.querySelector('input[name="type"]:checked')?.value;
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">Select Category</option>';
                    
                    const arr = (selectedType && categories && Array.isArray(categories[selectedType])) ? categories[selectedType] : [];
                    if (arr.length) {
                        // Remove duplicates and sort
                        const uniqueCategories = [...new Set(arr)].sort();
                        uniqueCategories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category;
                            option.textContent = category;
                            categorySelect.appendChild(option);
                        });
                    }
                }
            }
            
            // Add event listeners to radio buttons
            if (typeRadios && categorySelect) {
                typeRadios.forEach(radio => {
                    radio.addEventListener('change', function() {
                        updateCategories();
                        updatePriorityBankCategory();
                    });
                });
                
                // Initialize categories on modal open (expense is default)
                updateCategories();
                // Initialize Priority Bank category when modal opens with Bank selected
                setTimeout(function() {
                    if (getSourceSystemId() === 'priority_bank') {
                        updatePriorityBankCategory();
                    }
                }, 100);
            }

            // Handle form submission
            if (transactionForm) {
                transactionForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Clear previous errors
                    document.querySelectorAll('.error-message').forEach(el => {
                        el.style.display = 'none';
                        el.textContent = '';
                    });
                    
                    // Disable submit button
                    const submitBtn = document.getElementById('submitBtn');
                    const submitBtnText = document.getElementById('submitBtnText');
                    const submitBtnLoader = document.getElementById('submitBtnLoader');
                    
                    if (submitBtn) submitBtn.disabled = true;
                    if (submitBtnText) submitBtnText.style.display = 'none';
                    if (submitBtnLoader) submitBtnLoader.style.display = 'inline';
                    
                    // Get form data
                    const formData = new FormData(transactionForm);
                    
                    // If Priority Bank is selected, use the hidden category input instead of the select
                    const systemId = getSourceSystemId();
                    const categorySelect = document.getElementById('modal_category');
                    
                    // Clear any existing category from formData to avoid duplicates
                    formData.delete('category');
                    
                    if (systemId === 'priority_bank') {
                        // Ensure Priority Bank category is updated before submission
                        updatePriorityBankCategory();
                        const priorityBankCategoryInput = document.getElementById('priorityBankCategoryInput');
                        if (priorityBankCategoryInput && priorityBankCategoryInput.value) {
                            formData.set('category', priorityBankCategoryInput.value);
                        } else {
                            // Fallback: set based on type
                            const selectedType = document.querySelector('input[name="type"]:checked')?.value;
                            formData.set('category', selectedType === 'income' ? 'Savings' : 'Loan');
                        }
                    } else {
                        // For non-Priority Bank, use the select value
                        if (categorySelect) {
                            // Ensure the name attribute is set
                            if (!categorySelect.hasAttribute('name')) {
                                categorySelect.setAttribute('name', 'category');
                            }
                            // Ensure it's visible and enabled
                            categorySelect.style.display = '';
                            categorySelect.disabled = false;
                            
                            if (!categorySelect.value || categorySelect.value === '') {
                                // If category select is empty, show error
                                showError('category', 'Please select a category');
                                if (submitBtn) submitBtn.disabled = false;
                                if (submitBtnText) submitBtnText.style.display = 'inline';
                                if (submitBtnLoader) submitBtnLoader.style.display = 'none';
                                return;
                            }
                            // Set the category value in formData
                            formData.set('category', categorySelect.value);
                        } else {
                            // If category select doesn't exist, show error
                            showError('category', 'Please select a category');
                            if (submitBtn) submitBtn.disabled = false;
                            if (submitBtnText) submitBtnText.style.display = 'inline';
                            if (submitBtnLoader) submitBtnLoader.style.display = 'none';
                            return;
                        }
                    }
                    
                    // Add CSRF token
                    const csrfToken = document.querySelector('meta[name="csrf-token"]');
                    if (csrfToken) {
                        formData.append('_token', csrfToken.getAttribute('content'));
                    }
                    
                    // Submit via AJAX
                    fetch('{{ route("transactions.store") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        const contentType = response.headers.get('content-type');
                        const isJson = contentType && contentType.indexOf('application/json') !== -1;
                        if (!response.ok) {
                            if (isJson) {
                                return response.json().then(function(data) {
                                    throw { status: response.status, data: data };
                                });
                            }
                            if (response.status === 419) {
                                throw { status: 419, data: { message: 'Session expired. Please refresh the page and try again.' } };
                            }
                            throw { status: response.status, data: { message: 'Request failed. Please refresh and try again.' } };
                        }
                        if (isJson) {
                            return response.json();
                        }
                        throw { status: response.status, data: { message: 'Invalid response. The transaction may have been saved—please refresh the page.' } };
                    })
                    .then(function(data) {
                        if (data && data.success) {
                            showNotification(data.message || 'Transaction saved successfully!');
                            closeModal();
                            setTimeout(function() {
                                window.location.reload();
                            }, 800);
                        } else {
                            showNotification('An error occurred. Please try again.', 'error');
                            if (submitBtn) submitBtn.disabled = false;
                            if (submitBtnText) submitBtnText.style.display = 'inline';
                            if (submitBtnLoader) submitBtnLoader.style.display = 'none';
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        if (error && error.data && error.data.errors) {
                            Object.keys(error.data.errors).forEach(function(field) {
                                const errorEl = document.getElementById('error_' + field);
                                if (errorEl) {
                                    errorEl.textContent = error.data.errors[field][0];
                                    errorEl.style.display = 'block';
                                }
                            });
                            showNotification(error.data.message || 'Please fix the errors above.', 'error');
                        } else {
                            const msg = (error && error.data && error.data.message) ? error.data.message : 'An error occurred. Please try again.';
                            showNotification(msg, 'error');
                        }
                        if (submitBtn) submitBtn.disabled = false;
                        if (submitBtnText) submitBtnText.style.display = 'inline';
                        if (submitBtnLoader) submitBtnLoader.style.display = 'none';
                    });
                });
            }

            // Show notification
            function showNotification(message, type = 'success') {
                const messageEl = document.getElementById('notificationMessage');
                if (messageEl) {
                    messageEl.textContent = message;
                }
                
                if (notificationToast) {
                    // Update border color based on type
                    if (type === 'error') {
                        notificationToast.style.borderLeftColor = 'var(--danger)';
                        const icon = notificationToast.querySelector('i');
                        if (icon) {
                            icon.className = 'fas fa-exclamation-circle';
                            icon.style.color = 'var(--danger)';
                        }
                    } else {
                        notificationToast.style.borderLeftColor = 'var(--success)';
                        const icon = notificationToast.querySelector('i');
                        if (icon) {
                            icon.className = 'fas fa-check-circle';
                            icon.style.color = 'var(--success)';
                        }
                    }
                    
                    notificationToast.style.display = 'block';
                    
                    // Auto hide after 5 seconds
                    setTimeout(() => {
                        notificationToast.style.display = 'none';
                    }, 5000);
                }
            }

            // Close notification
            if (closeNotificationBtn) {
                closeNotificationBtn.addEventListener('click', function() {
                    if (notificationToast) {
                        notificationToast.style.display = 'none';
                    }
                });
            }

            // Hover effect for new transaction button
            if (newTransactionBtn) {
                newTransactionBtn.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.1)';
                });
                newTransactionBtn.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            }
        })();
        @endif
    </script>

    <!-- Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(err => {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
