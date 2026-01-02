<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IncomeApiController;
use App\Http\Controllers\Api\ExpenseApiController;
use App\Http\Controllers\Api\LoanApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\CentralFinanceApiController;

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

// Central Finance API - Public endpoints for external systems
// These use token-based authentication (API tokens, not user sessions)
Route::prefix('central-finance')->middleware('auth:sanctum')->group(function () {
    Route::post('/income', [CentralFinanceApiController::class, 'storeIncome']);
    Route::post('/expense', [CentralFinanceApiController::class, 'storeExpense']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Income endpoints
    Route::post('/income', [IncomeApiController::class, 'store']);
    Route::get('/income', [IncomeApiController::class, 'index']);

    // Expense endpoints
    Route::post('/expenses', [ExpenseApiController::class, 'store']);
    Route::get('/expenses', [ExpenseApiController::class, 'index']);

    // Loan endpoints
    Route::post('/loans', [LoanApiController::class, 'store']);
    Route::get('/loans', [LoanApiController::class, 'index']);
    Route::post('/loans/{loan}/return', [LoanApiController::class, 'markReturned']);
    Route::post('/loans/{loan}/lost', [LoanApiController::class, 'markLost']);

    // Dashboard summary endpoint
    Route::get('/dashboard/summary', [DashboardApiController::class, 'summary']);
});
