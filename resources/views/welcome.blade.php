<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Priority Bank Ghana') }}</title>

          <!-- Favicon (multiple versions for different devices) -->
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
< <!-- Web App Manifest -->
  <link rel="manifest" href="{{ asset('manifest.json') }}">
  
  <!-- Theme Color (matches manifest) -->
  <meta name="theme-color" content="#4f46e5">
  
  <!-- iOS Support -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="{{ asset('pbg_logo_192.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Scripts -->
    @stack('scripts')
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
        <!-- Application Logo -->
        <div class="w-full sm:max-w-md px-6 py-4">
            <a href="{{ url('/') }}">
                <x-application-logo class="w-20 h-20 fill-current text-gray-700 dark:text-gray-200 mx-auto" />
            </a>
        </div>

        <!-- Page Content -->
        <div class="w-full sm:max-w-md px-6 py-8 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
            <!-- Authentication Links -->
            @if (Route::has('login'))
                <div class="flex flex-col space-y-4">
                    @auth
                        <a
                            href="{{ route('dashboard') }}"
                            class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Go to Dashboard
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="w-full flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="w-full flex items-center justify-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </div>
            @endif

            <!-- App Features -->
            <div class="mt-8 text-center">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Priority Bank Ghana Features
                </h2>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-medium text-indigo-600 dark:text-indigo-400">Income Tracking</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Monitor all your income sources in one place
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-medium text-indigo-600 dark:text-indigo-400">Expense Management</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Categorize and analyze your spending
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-medium text-indigo-600 dark:text-indigo-400">Loan Tracking</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Manage both given and received loans
                        </p>
                    </div>

                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h3 class="font-medium text-indigo-600 dark:text-indigo-400">AI Insights</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                            Get smart financial recommendations
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} Priority Bank Ghana. All rights reserved.
        </div>
    </div>
</body>
</html>