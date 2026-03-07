<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Priority Bank') }} - @yield('title')</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <!-- Favicons -->
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Compiled Assets -->
    <link rel="stylesheet" href="{{ asset('build/assets/app-BcwfKLHV.css') }}">
    <script src="{{ asset('build/assets/app-DaBYqt0m.js') }}" defer></script>
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --secondary: #10b981;
            --accent: #f59e0b;
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
            --shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Montserrat', sans-serif;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
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
        }

        .auth-container {
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
        }

        .auth-logo {
            display: grid;
            grid-template-columns: 4fr 8fr;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            width: 100%;
            max-width: 100%;
        }

        .auth-logo img {
            width: 100%;
            max-width: 60px;
            height: auto;
            aspect-ratio: 1;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            justify-self: center;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .auth-logo img:hover {
            transform: scale(1.05);
            box-shadow: 0 12px 35px rgba(0,0,0,0.3);
        }

        .auth-logo a {
            display: contents;
            text-decoration: none;
        }

        .auth-logo-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0; /* Allows text to shrink properly */
        }

        .auth-logo h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 0.25rem;
            line-height: 1.2;
        }

        .auth-logo p {
            color: rgba(255,255,255,0.8);
            font-size: 0.85rem;
            line-height: 1.3;
        }

        .auth-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            padding: 2.5rem;
            backdrop-filter: blur(10px);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h2 {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .auth-header p {
            color: var(--gray-600);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group-full {
            grid-column: 1 / -1;
        }

        @media (min-width: 640px) {
            .form-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            color: var(--gray-700);
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .form-label i {
            color: var(--primary);
            font-size: 1rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Montserrat', sans-serif;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-control::placeholder {
            color: var(--gray-400);
        }

        .form-error {
            color: #ef4444;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-error i {
            font-size: 0.75rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid var(--gray-300);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .form-check-input:checked {
            background: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            color: var(--gray-600);
            font-size: 0.9rem;
            cursor: pointer;
        }

        .btn-auth {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.3);
        }

        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.4);
        }

        .btn-auth:active {
            transform: translateY(0);
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .auth-links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .auth-links p {
            color: var(--gray-600);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .forgot-password {
            text-align: right;
            margin-bottom: 1rem;
        }

        .forgot-password a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-password a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .auth-card {
                padding: 2rem 1.5rem;
            }

            .auth-header h2 {
                font-size: 1.5rem;
            }

            .auth-logo {
                grid-template-columns: 4fr 8fr;
                justify-items: center;
            }

            .auth-logo img {
                max-width: 50px;
            }

            .auth-logo h1 {
                font-size: 1.25rem;
            }

            .auth-logo p {
                font-size: 0.8rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="auth-container">
        <div class="auth-logo">
            <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Logo">
            <div class="auth-logo-content">
                <h1>Priority Bank</h1>
                <p>Save together. Grow together.</p>
            </div>
        </div>

        <div class="auth-card">
            <div class="auth-header">
                <h2>@yield('title', 'Welcome')</h2>
                <p>@yield('subtitle', '')</p>
            </div>

            @if(session('status'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: var(--radius); padding: 1rem; margin-bottom: 1.5rem; color: var(--success); font-size: 0.9rem;">
                    <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</body>
</html>
