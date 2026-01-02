<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Priority Bank Ghana') }} - Priority Savings Group</title>
    <meta name="description" content="Join Priority Bank Ghana's Priority Savings Group - A trusted community where friends help friends achieve financial goals through collaborative savings and responsible lending.">

    <!-- Favicon (multiple versions for different devices) -->
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('pbg_logo.png') }}" type="image/x-icon">

    <!-- Web App Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}" crossorigin="use-credentials">

    <!-- Theme Color (matches manifest) -->
    <meta name="theme-color" content="#4f46e5">

    <!-- iOS Support -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('pbg_logo_192.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Compiled Assets -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BcwfKLHV.css') }}">
    <script src="{{ asset('build/assets/app-DaBYqt0m.js') }}" defer></script>

    <!-- Scripts -->
    @stack('scripts')
</head>
<body class="font-sans antialiased bg-gradient-to-br from-blue-50 to-indigo-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ url('/') }}" class="flex items-center">
                            <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Ghana Logo" class="w-10 h-10 mr-3">
                            <span class="text-xl font-bold text-gray-900">Priority Bank</span>
                        </a>
                    </div>
                </div>

                <!-- Auth Links -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium transition duration-150">
                            Join Now
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-indigo-600 to-purple-700 text-white">
        <div class="absolute inset-0 bg-black bg-opacity-20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Welcome to Your<br>
                    <span class="text-yellow-300">Priority Savings Group</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto leading-relaxed">
                    Join Priority Bank Ghana's exclusive community where friends help friends achieve financial goals through collaborative savings and responsible lending.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ route('dashboard') }}" class="bg-yellow-400 hover:bg-yellow-500 text-indigo-900 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300 shadow-lg">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="bg-yellow-400 hover:bg-yellow-500 text-indigo-900 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300 shadow-lg">
                            Join Our Community
                        </a>
                        <a href="#features" class="border-2 border-white text-white hover:bg-white hover:text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300">
                            Learn More
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg">
                    <div class="text-3xl font-bold text-blue-600 mb-2">50+</div>
                    <div class="text-gray-600">Community Members</div>
                </div>
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg">
                    <div class="text-3xl font-bold text-green-600 mb-2">GHS 25,000</div>
                    <div class="text-gray-600">Available Funds</div>
                </div>
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg">
                    <div class="text-3xl font-bold text-purple-600 mb-2">30+</div>
                    <div class="text-gray-600">Loans Granted</div>
                </div>
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-lg">
                    <div class="text-3xl font-bold text-yellow-600 mb-2">100+</div>
                    <div class="text-gray-600">Successful Payments</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Why Join Our Savings Group?
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Experience the power of community finance with features designed for mutual benefit and financial growth.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-blue-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Smart Savings</h3>
                    <p class="text-gray-600">
                        Deposit funds to make them available for lending and earn interest while helping friends achieve their goals.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-green-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Easy Loan Requests</h3>
                    <p class="text-gray-600">
                        Apply for loans from the community fund with transparent processes and fair interest rates set by our administrators.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-purple-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Secure & Transparent</h3>
                    <p class="text-gray-600">
                        All transactions are tracked and verified. Enjoy peace of mind with our secure payment gateways and transparent processes.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-yellow-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Real-time Balance Tracking</h3>
                    <p class="text-gray-600">
                        Monitor your savings balance (positive) and loan balance (negative) in real-time with our comprehensive dashboard.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-red-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Community Support</h3>
                    <p class="text-gray-600">
                        Join a trusted community of friends where everyone supports each other's financial growth and success.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-white p-8 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    <div class="bg-indigo-100 w-16 h-16 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Financial Insights</h3>
                    <p class="text-gray-600">
                        Get AI-powered financial insights and recommendations to make better financial decisions for yourself and the community.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    How It Works
                </h2>
                <p class="text-xl text-gray-600">
                    Simple steps to join our financial community
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                        1
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Join the Community</h3>
                    <p class="text-gray-600">
                        Register for your free account and become part of our trusted financial community.
                    </p>
                </div>

                <div class="text-center">
                    <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                        2
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Save & Contribute</h3>
                    <p class="text-gray-600">
                        Make deposits to contribute to the community fund and make money available for lending.
                    </p>
                </div>

                <div class="text-center">
                    <div class="bg-indigo-600 text-white w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
                        3
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Access & Support</h3>
                    <p class="text-gray-600">
                        Request loans when needed and repay conveniently through our secure payment systems.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-indigo-600 to-purple-700 text-white py-20">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                Ready to Join Our Savings Group?
            </h2>
            <p class="text-xl mb-8 opacity-90">
                Start your journey towards financial freedom with friends you can trust.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @auth
                    <a href="{{ route('dashboard') }}" class="bg-yellow-400 hover:bg-yellow-500 text-indigo-900 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300 shadow-lg">
                        Access Your Dashboard
                    </a>
                @else
                    <a href="{{ route('register') }}" class="bg-yellow-400 hover:bg-yellow-500 text-indigo-900 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300 shadow-lg">
                        Join Our Community
                    </a>
                    <a href="{{ route('login') }}" class="border-2 border-white text-white hover:bg-white hover:text-indigo-600 px-8 py-4 rounded-lg font-semibold text-lg transition duration-300">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Ghana Logo" class="w-10 h-10 mr-3">
                        <span class="text-xl font-bold">Priority Bank</span>
                    </div>
                    <p class="text-gray-300 mb-4">
                        Building financial futures through community trust and collaborative savings.
                    </p>
                    <p class="text-sm text-gray-400">
                        Join our exclusive savings group where friends help friends achieve financial goals.
                    </p>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        @auth
                            <li><a href="{{ route('dashboard') }}" class="text-gray-300 hover:text-white transition duration-150">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition duration-150">Sign In</a></li>
                            <li><a href="{{ route('register') }}" class="text-gray-300 hover:text-white transition duration-150">Join Now</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact</h3>
                    <div class="text-gray-300 space-y-2">
                        <p>📧 support@prioritybank.com</p>
                        <p>📞 +233 XX XXX XXXX</p>
                        <p>📍 Accra, Ghana</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Priority Bank Ghana. All rights reserved. | Building financial futures together.</p>
            </div>
        </div>
    </footer>
</body>
</html>