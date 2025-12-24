<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'openai.errors'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    // Admin only routes
    Route::middleware('can:admin')->group(function () {
        Route::resource('interest-rates', \App\Http\Controllers\InterestRatesController::class);
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
