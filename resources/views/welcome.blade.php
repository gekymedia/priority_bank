<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Priority Finance - Save together. Grow together.</title>
    <meta name="description" content="A simple way for friends to save money together, support each other, and reach shared financial goals — no stress, no pressure.">
    
    <!-- Favicons -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('pbg_logo.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('pbg_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --secondary: #10b981;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --success: #10b981;
            --danger: #ef4444;
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
            --radius: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--secondary) 33.33%, var(--accent) 33.33%, var(--accent) 66.66%, var(--primary) 66.66%);
            z-index: 1000;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1440 320'%3E%3Cpath fill='rgba(255,255,255,0.03)' d='M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,165.3C1248,181,1344,171,1392,165.3L1440,160L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z'%3E%3C/path%3E%3C/svg%3E") no-repeat bottom;
            opacity: 0.3;
            z-index: 0;
            pointer-events: none;
        }

        /* Navigation */
        .nav {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            position: fixed;
            top: 5px;
            left: 0;
            right: 0;
            z-index: 999;
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
            transition: all 0.3s;
        }

        .nav.scrolled {
            box-shadow: var(--shadow-md);
            background: white;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 800;
            font-size: 1.35rem;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-brand img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .nav-links a {
            color: var(--gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.2s;
        }

        .nav-links a:hover { color: var(--primary); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover { 
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-white {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow-md);
        }
        .btn-white:hover {
            background: var(--gray-100);
            transform: translateY(-2px);
        }

        .btn-lg {
            padding: 1rem 2rem;
            font-size: 1.05rem;
        }

        .btn-xl {
            padding: 1.25rem 2.5rem;
            font-size: 1.1rem;
        }

        /* Hero Section */
        .hero {
            padding: 12rem 1.5rem 6rem;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .hero-logo {
            margin-bottom: 2rem;
        }

        .hero-logo img {
            width: 100px;
            height: 100px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 1.5rem;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            line-height: 1.15;
            margin-bottom: 1.5rem;
        }

        .hero-content p {
            font-size: 1.3rem;
            color: rgba(255,255,255,0.95);
            line-height: 1.8;
            max-width: 900px;
            margin: 0 auto 2.5rem;
        }

        .hero-content p strong {
            font-size: 1.5rem;
            display: block;
            margin-bottom: 1rem;
            color: white;
        }

        .hero-content p span {
            display: block;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .hero-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        /* Features Section */
        .features {
            padding: 6rem 1.5rem;
            background: white;
            position: relative;
            z-index: 1;
        }

        .section-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-header {
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
        }

        .feature-card {
            background: var(--gray-50);
            border-radius: var(--radius-lg);
            padding: 2.5rem;
            transition: all 0.3s;
            border: 2px solid transparent;
            text-align: center;
        }

        .feature-card:hover {
            background: white;
            border-color: var(--primary-light);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.75rem;
        }

        .feature-description {
            color: var(--gray-600);
            line-height: 1.7;
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 4rem 1.5rem 2rem;
            position: relative;
            z-index: 1;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            margin-bottom: 1rem;
        }

        .footer-brand img {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .footer-description {
            color: rgba(255,255,255,0.7);
            line-height: 1.7;
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        .footer-bottom {
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.5);
            font-size: 0.9rem;
        }

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero-content h1 {
                font-size: 2.75rem;
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero {
                padding: 10rem 1.5rem 4rem;
            }

            .hero-content h1 {
                font-size: 2.25rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .hero-content p strong {
                font-size: 1.3rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
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

        .fade-in-up {
            animation: fadeInUp 0.8s ease forwards;
        }

        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav" id="navbar">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="nav-brand">
                <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Logo">
                Priority Bank
            </a>
            
            <div class="nav-links">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">
                        <i class="fas fa-th-large"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}">Sign In</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Join Now</a>
                @endauth
            </div>

            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-logo fade-in-up">
                <img src="{{ asset('pbg_logo.png') }}" alt="Priority Finance Logo">
            </div>
            <div class="hero-content fade-in-up delay-1">
                <h1>Priority Finance</h1>
                <p>
                    <strong>Save together. Grow together.</strong>
                    <span>Priority Savings Group</span>
                    A simple way for friends to save money together, support each other, and reach shared financial goals — no stress, no pressure.
                </p>
                
                <div class="hero-actions">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-white btn-xl">
                            <i class="fas fa-th-large"></i>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-white btn-xl">
                            <i class="fas fa-user-plus"></i>
                            Join Now
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="section-container">
            <div class="section-header">
                <h2 class="section-title">Why Our circle of friends Love It Here</h2>
            </div>

            <div class="features-grid">
                <div class="feature-card fade-in-up delay-1">
                    <div class="feature-icon">💰</div>
                    <h3 class="feature-title">Save Smarter, Together</h3>
                    <p class="feature-description">
                        Put money aside with friends and watch your savings grow at your own pace.
                    </p>
                </div>

                <div class="feature-card fade-in-up delay-2">
                    <div class="feature-icon">🔒</div>
                    <h3 class="feature-title">Clear & Honest</h3>
                    <p class="feature-description">
                        Every contribution and payment is visible, tracked, and easy to understand.
                    </p>
                </div>

                <div class="feature-card fade-in-up delay-3">
                    <div class="feature-icon">🤝</div>
                    <h3 class="feature-title">Friendly Loans</h3>
                    <p class="feature-description">
                        Need support? Get access to community loans with fair terms everyone agrees on.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-brand">
                <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Logo">
                Priority Bank
            </div>
            <p class="footer-description">
                Built by Geky friends, powered by trust.
            </p>
            <div class="footer-bottom">
                <p>&copy; 2026 Priority Bank Ghana</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html>
