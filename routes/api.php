<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\CentralFinanceApiController;
use App\Http\Controllers\Api\SikaWalletApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check (public)
Route::get('/health', [SikaWalletApiController::class, 'health']);

// Sika Wallet API - For GekyChat integration
// Uses API key authentication via middleware
Route::prefix('wallets')->middleware('auth:sanctum')->group(function () {
    Route::get('/user/{userId}/balance', [SikaWalletApiController::class, 'getBalance']);
    Route::get('/user/{userId}/transactions', [SikaWalletApiController::class, 'getTransactions']);
    Route::post('/debit', [SikaWalletApiController::class, 'debit']);
    Route::post('/credit', [SikaWalletApiController::class, 'credit']);
    Route::post('/deposit', [SikaWalletApiController::class, 'deposit']);
});

Route::prefix('transactions')->middleware('auth:sanctum')->group(function () {
    Route::get('/{transactionId}', [SikaWalletApiController::class, 'verifyTransaction']);
    Route::post('/reverse', [SikaWalletApiController::class, 'reverseTransaction']);
});

// Central Finance API - Public endpoints for external systems
// These use token-based authentication (API tokens, not user sessions)
Route::prefix('central-finance')->middleware('auth:sanctum')->group(function () {
    Route::get('/balance', [CentralFinanceApiController::class, 'balance']);
    Route::post('/income', [CentralFinanceApiController::class, 'storeIncome']);
    Route::post('/expense', [CentralFinanceApiController::class, 'storeExpense']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Loan endpoints
    Route::post('/loans', [LoanApiController::class, 'store']);
    Route::get('/loans', [LoanApiController::class, 'index']);
    Route::post('/loans/{loan}/return', [LoanApiController::class, 'markReturned']);
    Route::post('/loans/{loan}/lost', [LoanApiController::class, 'markLost']);

    // Dashboard summary endpoint
    Route::get('/dashboard/summary', [DashboardApiController::class, 'summary']);
});
