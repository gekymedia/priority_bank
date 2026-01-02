<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
 <!-- Correct manifest link tag -->
    <link rel="manifest" href="{{ asset('manifest.json') }}" crossorigin="use-credentials">
    
    <!-- Required PWA meta tags -->
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
  <!-- Theme Color (matches manifest) -->
  <meta name="theme-color" content="#4f46e5">
  
  <!-- iOS Support -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="apple-touch-icon" href="{{ asset('pbg_logo_192.png') }}">

       <!-- Favicon (multiple versions for different devices) -->
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="32x32">
<link rel="icon" href="{{ asset('pbg_logo.png') }}" type="image/png" sizes="16x16">
<link rel="apple-touch-icon" href="{{ asset('pbg_logo.png') }}">
<link rel="shortcut icon" href="{{ asset('pbg_logo.png') }}" type="image/x-icon">
    <title>{{ config('app.name', 'Priority Bank') }} - @yield('title')</title>


        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Compiled Assets -->
        <link rel="stylesheet" href="{{ asset('build/assets/app-BcwfKLHV.css') }}">
        <script src="{{ asset('build/assets/app-DaBYqt0m.js') }}" defer></script>
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
               <a href="{{ url('/') }}">
    <img src="{{ asset('pbg_logo.png') }}" alt="Priority Bank Ghana Logo" class="w-20 h-20 mx-auto">
</a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
