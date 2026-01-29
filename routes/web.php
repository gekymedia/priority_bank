<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Welcome page
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isAdmin()) {
            $response = redirect()->route('admin.dashboard');
        } else {
            $response = redirect()->route('dashboard');
        }
    } else {
        $response = response()->view('welcome');
    }
    
    // Add cache-control headers to prevent browser caching
    $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
    $response->headers->set('Pragma', 'no-cache');
    $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    
    return $response;
});

// Legal Pages
Route::view('/privacy-policy', 'legal.privacy-policy')->name('privacy.policy');
Route::view('/terms-of-service', 'legal.terms-of-service')->name('terms.service');

// User Dashboard (redirect admins to admin dashboard, check approval)
Route::get('/dashboard', function () {
    if (auth()->user()->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return app(DashboardController::class)->userDashboard();
})->middleware(['auth', 'approved'])->name('dashboard');

// Admin Dashboard
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('dashboard');
    
    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);
    Route::post('users/{user}/approve', [\App\Http\Controllers\UserController::class, 'approve'])->name('users.approve');
    Route::post('users/{user}/reject', [\App\Http\Controllers\UserController::class, 'reject'])->name('users.reject');
    Route::post('users/{user}/impersonate', [\App\Http\Controllers\UserController::class, 'impersonate'])->name('users.impersonate');
    
    // Fund Sources
    Route::get('/fund-sources', [\App\Http\Controllers\FundSourceController::class, 'index'])->name('fund-sources.index');
    Route::post('/fund-sources/transfer', [\App\Http\Controllers\FundSourceController::class, 'transfer'])->name('fund-sources.transfer');
    
    // System Registry (Sources) Management
    Route::post('sources', [\App\Http\Controllers\SystemRegistryController::class, 'store'])->name('sources.store');
    Route::put('sources/{systemRegistry}', [\App\Http\Controllers\SystemRegistryController::class, 'update'])->name('sources.update');
    Route::delete('sources/{systemRegistry}', [\App\Http\Controllers\SystemRegistryController::class, 'destroy'])->name('sources.destroy');
});

Route::middleware(['auth', 'approved'])->group(function () {
    // Stop impersonating route (accessible even when impersonating)
    Route::post('admin/users/stop-impersonating', [\App\Http\Controllers\UserController::class, 'stopImpersonating'])->name('admin.users.stop-impersonating');
    
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

    // Category Management routes
    Route::resource('income-categories', \App\Http\Controllers\IncomeCategoryController::class)->except(['show']);
    Route::resource('expense-categories', \App\Http\Controllers\ExpenseCategoryController::class)->except(['show']);
    
    // Unified Category Management (Admin only)
    Route::middleware('admin')->group(function () {
        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['show', 'create', 'edit']);
    });

    // Credit Union routes
    Route::resource('savings', \App\Http\Controllers\SavingsController::class);
    Route::post('savings/withdraw', [\App\Http\Controllers\SavingsController::class, 'withdraw'])->name('savings.withdraw');
    Route::get('savings/callback/{gateway}', [\App\Http\Controllers\SavingsController::class, 'callback'])->name('savings.callback');
    Route::post('savings/{saving}/retry-payment', [\App\Http\Controllers\SavingsController::class, 'retryPayment'])->name('savings.retry-payment');
    
    // Admin approval routes for savings deposits
    Route::middleware('admin')->group(function () {
        Route::post('savings/{saving}/approve', [\App\Http\Controllers\SavingsController::class, 'approve'])->name('savings.approve');
        Route::post('savings/{saving}/reject', [\App\Http\Controllers\SavingsController::class, 'reject'])->name('savings.reject');
        Route::post('savings/{saving}/mark-as-failed', [\App\Http\Controllers\SavingsController::class, 'markAsFailed'])->name('savings.mark-as-failed');
    });
    Route::post('savings/{saving}/try-again', [\App\Http\Controllers\SavingsController::class, 'tryAgain'])->name('savings.try-again');
    Route::resource('loan-requests', \App\Http\Controllers\LoanRequestsController::class);
    Route::post('loan-requests/{loan_request}/approve', [\App\Http\Controllers\LoanRequestsController::class, 'approve'])->name('loan-requests.approve');
    Route::post('loan-requests/{loan_request}/reject', [\App\Http\Controllers\LoanRequestsController::class, 'reject'])->name('loan-requests.reject');
    Route::post('loan-requests/{loan_request}/record-payment', [\App\Http\Controllers\LoanRequestsController::class, 'recordPayment'])->name('loan-requests.record-payment');
    Route::resource('deposits', \App\Http\Controllers\DepositController::class);
    Route::post('deposits/{deposit}/approve', [\App\Http\Controllers\DepositController::class, 'approve'])->name('deposits.approve');
    Route::post('deposits/{deposit}/reject', [\App\Http\Controllers\DepositController::class, 'reject'])->name('deposits.reject');
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
