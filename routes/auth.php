<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// GekyChat SSO - Auto-login via phone number (Sika Wallet integration)
Route::get('auth/gekychat-sso', function (\Illuminate\Http\Request $request) {
    $phone = $request->query('phone');
    $source = $request->query('source');
    $autoLogin = $request->query('auto_login') === 'true';
    
    // Validate request is from GekyChat
    if ($source !== 'gekychat' || !$phone) {
        return redirect()->route('login')->with('error', 'Invalid SSO request');
    }
    
    // Find user by phone number
    $user = \App\Models\User::where('phone', $phone)->first();
    
    if (!$user) {
        // User doesn't exist - redirect to register with phone pre-filled
        return redirect()->route('register')->with([
            'phone' => $phone,
            'from_gekychat' => true,
            'message' => 'Create a Sika Wallet account to continue',
        ]);
    }
    
    // Auto-login the user
    if ($autoLogin) {
        \Illuminate\Support\Facades\Auth::login($user);
        $request->session()->regenerate();
        
        // Check if there's a recipient to send Sika to
        $recipientId = $request->query('recipient_id');
        $recipientName = $request->query('recipient_name');
        
        if ($recipientId || $recipientName) {
            // Redirect to transfer page with recipient info
            return redirect()->route('dashboard')->with([
                'send_sika' => true,
                'recipient_id' => $recipientId,
                'recipient_name' => $recipientName,
            ]);
        }
        
        return redirect()->intended(route('dashboard'));
    }
    
    return redirect()->route('login')->with('phone', $phone);
})->name('gekychat.sso');

Route::middleware(['guest', 'prevent.auth.cache'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware(['auth', 'prevent.auth.cache'])->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
    
    // GET logout route as fallback (handles logout directly, CSRF excluded for GET)
    Route::get('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout.get');
});
