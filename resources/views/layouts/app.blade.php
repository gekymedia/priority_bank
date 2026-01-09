<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Favicon (multiple versions for different devices) -->
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
<link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
<link rel="shortcut icon" href="{{ asset('pbg_logo.png') }}" type="image/x-icon">
    <title>{{ config('app.name', 'Priority Bank') }} - @yield('title')</title>
 <!-- Web App Manifest -->
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  
  <!-- Theme Color (matches manifest) -->
  <meta name="theme-color" content="#4f46e5">
  
  <!-- iOS Support -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="{{ asset('pbg_logo_192.png') }}">
<meta name="mobile-web-app-capable" content="yes">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Compiled Assets -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BcwfKLHV.css') }}">
    <script src="{{ asset('build/assets/app-DaBYqt0m.js') }}" defer></script>

    <!-- Additional CSS -->
    @stack('styles')
</head>
@php
    $theme = auth()->check() ? auth()->user()->theme : 'light';
    $isDark = $theme === 'dark';
@endphp
<body class="font-sans antialiased {{ $isDark ? 'bg-gray-900 text-gray-200' : 'bg-gray-50 text-gray-900' }}">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-64 bg-indigo-700 text-white">
                <div class="flex items-center justify-center h-16 px-4 bg-indigo-800">
                    <span class="text-xl font-bold">{{ config('app.name', 'PriorityBank') }}</span>
                </div>
                <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
                    <nav class="flex-1 space-y-2">
                        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('incomes.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('incomes.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-arrow-down mr-3"></i>
                            Income
                        </a>
                        <a href="{{ route('expenses.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('expenses.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-arrow-up mr-3"></i>
                            Expenses
                        </a>
                        <a href="{{ route('loans.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('loans.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-hand-holding-usd mr-3"></i>
                            Loans
                        </a>
                        <a href="{{ route('accounts.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('accounts.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-wallet mr-3"></i>
                            Accounts
                        </a>
                        <a href="{{ route('budgets.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('budgets.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-pie-chart mr-3"></i>
                            Budgets
                        </a>
                        <a href="{{ route('transactions.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('transactions.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-exchange-alt mr-3"></i>
                            Transactions
                        </a>
                        <a href="{{ route('api-keys.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('api-keys.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-key mr-3"></i>
                            API Keys
                        </a>
                        <div class="pt-4 mt-4 border-t border-indigo-600">
                            <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Categories</p>
                            <a href="{{ route('income-categories.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('income-categories.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                                <i class="fas fa-tags mr-3"></i>
                                Income Categories
                            </a>
                            <a href="{{ route('expense-categories.index') }}" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('expense-categories.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                                <i class="fas fa-tags mr-3"></i>
                                Expense Categories
                            </a>
                        </div>
                    </nav>
                </div>
                <div class="p-4 border-t border-indigo-600">
                    <div class="flex items-center">
                        @if(auth()->user()->profile_photo_path)
                            <img class="w-8 h-8 rounded-full object-cover" 
                                 src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" 
                                 alt="User avatar">
                        @else
                            <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        @endif
                        <div class="ml-3">
                            <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                            <a href="{{ route('profile.edit') }}" class="text-xs text-indigo-200 hover:text-white">View Profile</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top Navigation -->
            <header class="bg-white shadow-sm">
                <div class="flex items-center justify-between px-6 py-3">
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button id="mobile-menu-toggle" class="text-gray-500 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                    
                    <!-- Search bar -->
                    <div class="flex-1 max-w-md mx-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search...">
                        </div>
                    </div>
                    
                    <!-- Right side icons -->
                    <div class="flex items-center space-x-4">
                        <button class="text-gray-500 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-bell"></i>
                        </button>
                        <button class="text-gray-500 hover:text-gray-600 focus:outline-none">
                            <i class="fas fa-envelope"></i>
                        </button>
                        <!-- Theme toggle -->
                        <form method="POST" action="{{ route('theme.toggle') }}">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-gray-600 focus:outline-none" title="Toggle Theme">
                                @if($isDark)
                                    <i class="fas fa-sun"></i>
                                @else
                                    <i class="fas fa-moon"></i>
                                @endif
                            </button>
                        </form>
                        
                        <!-- User dropdown -->
                        <div class="relative ml-3">
                            <div class="flex items-center space-x-2 cursor-pointer" id="user-menu-button">
                                <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                                @if(auth()->user()->profile_photo_path)
                                    <img class="w-8 h-8 rounded-full object-cover" 
                                         src="{{ asset('storage/'.auth()->user()->profile_photo_path) }}" 
                                         alt="User avatar">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Dropdown menu -->
                            <div class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu">
                                <div class="py-1">
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Your Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" role="menuitem">Sign out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile sidebar (hidden by default) -->
            <div id="mobile-sidebar" class="md:hidden hidden fixed inset-0 z-50 bg-black bg-opacity-50">
                <div class="fixed left-0 top-0 bottom-0 w-64 bg-indigo-700 text-white overflow-y-auto">
                    <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-tachometer-alt mr-3"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('incomes.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('incomes.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-arrow-down mr-3"></i>
                        Income
                    </a>
                    <a href="{{ route('expenses.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('expenses.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-arrow-up mr-3"></i>
                        Expenses
                    </a>
                    <a href="{{ route('loans.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('loans.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-hand-holding-usd mr-3"></i>
                        Loans
                    </a>
                    <a href="{{ route('accounts.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('accounts.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-wallet mr-3"></i>
                        Accounts
                    </a>
                    <a href="{{ route('budgets.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('budgets.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-pie-chart mr-3"></i>
                        Budgets
                    </a>
                    <a href="{{ route('transactions.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('transactions.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-exchange-alt mr-3"></i>
                        Transactions
                    </a>
                    <a href="{{ route('api-keys.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('api-keys.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                        <i class="fas fa-key mr-3"></i>
                        API Keys
                    </a>
                    <div class="pt-4 mt-4 border-t border-indigo-600">
                        <p class="px-4 text-xs font-semibold text-indigo-300 uppercase tracking-wider mb-2">Categories</p>
                        <a href="{{ route('income-categories.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('income-categories.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-tags mr-3"></i>
                            Income Categories
                        </a>
                        <a href="{{ route('expense-categories.index') }}" class="block px-4 py-2 text-base font-medium {{ request()->routeIs('expense-categories.*') ? 'bg-indigo-600' : 'hover:bg-indigo-600' }}">
                            <i class="fas fa-tags mr-3"></i>
                            Expense Categories
                        </a>
                    </div>
                    </div>
                    <!-- Close button -->
                    <div class="px-4 py-3 border-t border-indigo-600">
                        <button id="mobile-menu-close" class="w-full text-left px-4 py-2 text-sm font-medium text-indigo-200 hover:text-white hover:bg-indigo-600 rounded">
                            <i class="fas fa-times mr-2"></i> Close Menu
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                @if (isset($header))
                    <div class="mb-6">
                        <h1 class="text-2xl font-bold text-gray-800">{{ $header }}</h1>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    @stack('scripts')
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileSidebar = document.getElementById('mobile-sidebar');
            const mobileMenuClose = document.getElementById('mobile-menu-close');
            
            function closeMobileMenu() {
                if (mobileSidebar) {
                    mobileSidebar.classList.add('hidden');
                }
            }
            
            function openMobileMenu() {
                if (mobileSidebar) {
                    mobileSidebar.classList.remove('hidden');
                }
            }
            
            if (mobileMenuToggle && mobileSidebar) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    openMobileMenu();
                });
                
                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', function(e) {
                        e.stopPropagation();
                        closeMobileMenu();
                    });
                }
                
                // Close mobile menu when clicking outside (on the overlay)
                mobileSidebar.addEventListener('click', function(e) {
                    if (e.target === mobileSidebar) {
                        closeMobileMenu();
                    }
                });
            }

            // User dropdown toggle
            const userMenuButton = document.getElementById('user-menu-button');
            if (userMenuButton) {
                userMenuButton.addEventListener('click', function() {
                    const menu = this.nextElementSibling;
                    if (menu) {
                        menu.classList.toggle('hidden');
                    }
                });
            }
        });
    </script>


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
</body>
</html>