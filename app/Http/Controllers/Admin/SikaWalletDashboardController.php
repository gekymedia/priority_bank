<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sika\SikaWallet;
use App\Models\Sika\SikaWalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SikaWalletDashboardController extends Controller
{
    public function index(Request $request)
    {
        // Summary Statistics
        $totalWallets = SikaWallet::count();
        $activeWallets = SikaWallet::where('status', 'active')->count();
        $totalBalance = SikaWallet::sum('balance');
        
        // Transaction Statistics
        $totalTransactions = SikaWalletTransaction::count();
        $completedTransactions = SikaWalletTransaction::where('status', 'COMPLETED')->count();
        $pendingTransactions = SikaWalletTransaction::where('status', 'PENDING')->count();
        $failedTransactions = SikaWalletTransaction::where('status', 'FAILED')->count();
        
        // Volume Statistics (Last 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        
        $totalCredits = SikaWalletTransaction::where('direction', 'CREDIT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('amount');
            
        $totalDebits = SikaWalletTransaction::where('direction', 'DEBIT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('amount');
        
        // Sika Coin Purchase Volume (money flowing to GekyChat)
        $sikaCoinPurchases = SikaWalletTransaction::where('type', 'SIKA_COIN_PURCHASE')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('amount');
            
        $sikaCoinPurchaseCount = SikaWalletTransaction::where('type', 'SIKA_COIN_PURCHASE')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        
        // Sika Coin Cashout Volume (money flowing from GekyChat)
        $sikaCoinCashouts = SikaWalletTransaction::where('type', 'SIKA_COIN_CASHOUT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('amount');
            
        $sikaCoinCashoutCount = SikaWalletTransaction::where('type', 'SIKA_COIN_CASHOUT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        
        // Deposit Volume
        $deposits = SikaWalletTransaction::where('type', 'DEPOSIT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->sum('amount');
            
        $depositCount = SikaWalletTransaction::where('type', 'DEPOSIT')
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->count();
        
        // Transaction Type Breakdown
        $transactionsByType = SikaWalletTransaction::select('type', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('type')
            ->get();
        
        // Daily Volume Chart Data (Last 14 days)
        $dailyVolume = SikaWalletTransaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN direction = "CREDIT" THEN amount ELSE 0 END) as credits'),
                DB::raw('SUM(CASE WHEN direction = "DEBIT" THEN amount ELSE 0 END) as debits'),
                DB::raw('COUNT(*) as count')
            )
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
        
        // Recent Transactions
        $recentTransactions = SikaWalletTransaction::with(['wallet'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
        
        // Top Wallets by Balance
        $topWallets = SikaWallet::orderBy('balance', 'desc')
            ->limit(10)
            ->get();
        
        // Top Users by Transaction Volume (Last 30 days)
        $topUsersByVolume = SikaWalletTransaction::select('external_user_id', 'source', DB::raw('SUM(amount) as total_volume'), DB::raw('COUNT(*) as transaction_count'))
            ->where('status', 'COMPLETED')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->groupBy('external_user_id', 'source')
            ->orderBy('total_volume', 'desc')
            ->limit(10)
            ->get();
        
        // Chart Data
        $chartLabels = $dailyVolume->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->toArray();
        $chartCredits = $dailyVolume->pluck('credits')->map(fn($v) => (float) $v)->toArray();
        $chartDebits = $dailyVolume->pluck('debits')->map(fn($v) => (float) $v)->toArray();
        
        return view('admin.sika-wallet.dashboard', compact(
            'totalWallets',
            'activeWallets',
            'totalBalance',
            'totalTransactions',
            'completedTransactions',
            'pendingTransactions',
            'failedTransactions',
            'totalCredits',
            'totalDebits',
            'sikaCoinPurchases',
            'sikaCoinPurchaseCount',
            'sikaCoinCashouts',
            'sikaCoinCashoutCount',
            'deposits',
            'depositCount',
            'transactionsByType',
            'recentTransactions',
            'topWallets',
            'topUsersByVolume',
            'chartLabels',
            'chartCredits',
            'chartDebits'
        ));
    }
    
    public function transactions(Request $request)
    {
        $query = SikaWalletTransaction::with(['wallet']);
        
        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }
        
        if ($request->filled('external_user_id')) {
            $query->where('external_user_id', $request->external_user_id);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('idempotency_key', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('external_user_id', $search);
            });
        }
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate(50);
        
        return view('admin.sika-wallet.transactions', compact('transactions'));
    }
    
    public function wallets(Request $request)
    {
        $query = SikaWallet::query();
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('external_user_id', $search);
        }
        
        $wallets = $query->orderBy('balance', 'desc')->paginate(50);
        
        return view('admin.sika-wallet.wallets', compact('wallets'));
    }
    
    public function walletDetails(SikaWallet $wallet)
    {
        $transactions = $wallet->transactions()
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        
        return view('admin.sika-wallet.wallet-details', compact('wallet', 'transactions'));
    }
}
