<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Legal Pages
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy.policy');
Route::view('/terms-of-service', 'legal.terms-of-service')->name('terms.service');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');

    // API Key Management routes
    Route::get('/api-keys', [\App\Http\Controllers\ApiKeyController::class, 'index'])->name('api-keys.index');
    Route::post('/api-keys', [\App\Http\Controllers\ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('/api-keys/{id}', [\App\Http\Controllers\ApiKeyController::class, 'destroy'])->name('api-keys.destroy');
    Route::get('/api-keys/documentation', [\App\Http\Controllers\ApiKeyController::class, 'documentation'])->name('api-keys.documentation');

    // Resource routes for financial modules
    Route::resource('incomes', \App\Http\Controllers\IncomeController::class);
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    Route::resource('loans', \App\Http\Controllers\LoanController::class);
    Route::post('loans/{loan}/return', [\App\Http\Controllers\LoanController::class, 'markReturned'])->name('loans.return');
    Route::post('loans/{loan}/lost', [\App\Http\Controllers\LoanController::class, 'markLost'])->name('loans.lost');
    Route::resource('accounts', \App\Http\Controllers\AccountController::class);
    Route::resource('budgets', \App\Http\Controllers\BudgetController::class);
    // Existing transactions resource
    Route::resource('transactions', \App\Http\Controllers\TransactionController::class);

    // Credit Union routes
    Route::resource('savings', \App\Http\Controllers\SavingsController::class);
    Route::resource('loan-requests', \App\Http\Controllers\LoanRequestsController::class);
    Route::post('loan-requests/{loan_request}/approve', [\App\Http\Controllers\LoanRequestsController::class, 'approve'])->name('loan-requests.approve');
    Route::post('loan-requests/{loan_request}/reject', [\App\Http\Controllers\LoanRequestsController::class, 'reject'])->name('loan-requests.reject');
    Route::resource('payments', \App\Http\Controllers\PaymentsController::class);

    // Payment gateway callbacks and webhooks
    Route::get('payments/callback/{gateway}', [\App\Http\Controllers\PaymentsController::class, 'callback'])->name('payments.callback');
    Route::post('payments/webhook/{gateway}', [\App\Http\Controllers\PaymentsController::class, 'webhook'])->name('payments.webhook');

    // Admin only routes
    Route::middleware('admin')->group(function () {
        Route::resource('interest-rates', \App\Http\Controllers\InterestRatesController::class);

        // Notifications
        Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'send'])->name('notifications.send');
    });

    // Theme toggle route
    Route::post('/theme/toggle', function (\Illuminate\Http\Request $request) {
        $user = $request->user();
        $user->theme = $user->theme === 'dark' ? 'light' : 'dark';
        $user->save();
        return back();
    })->name('theme.toggle');
});

require __DIR__.'/auth.php';
