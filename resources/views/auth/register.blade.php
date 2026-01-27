<x-guest-layout>
    @section('title', 'Create Account')
    @section('subtitle', 'Join Priority Savings Group and start saving together')

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-grid">
            <!-- Name -->
            <div class="form-group form-group-full">
                <label for="name" class="form-label">
                    <i class="fas fa-user"></i>
                    Full Name
                </label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control @error('name') is-invalid @enderror" 
                    value="{{ old('name') }}" 
                    placeholder="Enter your full name" 
                    required 
                    autofocus
                >
                @error('name')
                    <div class="form-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control @error('email') is-invalid @enderror" 
                    value="{{ old('email') }}" 
                    placeholder="Enter your email address" 
                    required
                >
                @error('email')
                    <div class="form-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Phone Number -->
            <div class="form-group">
                <label for="phone" class="form-label">
                    <i class="fas fa-phone"></i>
                    Phone Number
                </label>
                <input 
                    type="tel" 
                    id="phone" 
                    name="phone" 
                    class="form-control @error('phone') is-invalid @enderror" 
                    value="{{ old('phone') }}" 
                    placeholder="Enter your phone number" 
                    required
                >
                @error('phone')
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
                    placeholder="Create a strong password" 
                    required
                >
                @error('password')
                    <div class="form-error">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="password_confirmation" class="form-label">
                    <i class="fas fa-lock"></i>
                    Confirm Password
                </label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    class="form-control" 
                    placeholder="Confirm your password" 
                    required
                >
            </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-auth">
            <i class="fas fa-user-plus"></i>
            Create Account
        </button>

        <!-- Login Link -->
        <div class="auth-links">
            <p>Already have an account?</p>
            <a href="{{ route('login') }}">
                <i class="fas fa-sign-in-alt me-1"></i>Sign in instead
            </a>
        </div>
    </form>
</x-guest-layout>
