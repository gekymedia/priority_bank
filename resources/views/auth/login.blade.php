<x-guest-layout>
    @section('title', 'Sign In')
    @section('subtitle', 'Welcome back! Sign in to continue')

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Login (Email or Phone) -->
        <div class="form-group">
            <label for="login" class="form-label">
                <i class="fas fa-user"></i>
                Email or Phone Number
            </label>
            <input 
                type="text" 
                id="login" 
                name="login" 
                class="form-control @error('login') is-invalid @enderror" 
                value="{{ old('login') }}" 
                placeholder="Enter your email or phone number" 
                required 
                autofocus
            >
            @error('login')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">
                <i class="fas fa-lock"></i>
                Password
            </label>
            <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-control @error('password') is-invalid @enderror" 
                placeholder="Enter your password" 
                required
            >
            @error('password')
                <div class="form-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-group">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember me</label>
            </div>
        </div>

        <!-- Forgot Password -->
        @if (Route::has('password.request'))
            <div class="forgot-password">
                <a href="{{ route('password.request') }}">
                    <i class="fas fa-key me-1"></i>Forgot your password?
                </a>
            </div>
        @endif

        <!-- Submit Button -->
        <button type="submit" class="btn-auth">
            <i class="fas fa-sign-in-alt"></i>
            Sign In
        </button>

        <!-- Register Link -->
        <div class="auth-links">
            <p>Don't have an account?</p>
            <a href="{{ route('register') }}">
                <i class="fas fa-user-plus me-1"></i>Create an account
            </a>
        </div>
    </form>
</x-guest-layout>
