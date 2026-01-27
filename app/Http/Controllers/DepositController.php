<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Transaction;
use App\Models\SystemRegistry;
use App\Services\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositController extends Controller
{
    /**
     * Display a listing of deposits.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $deposits = Deposit::with('user')->latest()->paginate(20);
        } else {
            $deposits = Deposit::where('user_id', $user->id)->latest()->paginate(20);
        }

        return view('deposits.index', compact('deposits'));
    }

    /**
     * Show the form for creating a new deposit.
     */
    public function create()
    {
        return view('deposits.create');
    }

    /**
     * Store a newly created deposit.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        $deposit = Deposit::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'description' => $request->description,
            'status' => 'pending',
        ]);

        // Notify admins about the new deposit
        $notificationService = new AdminNotificationService();
        $user = Auth::user();
        $message = "New Deposit Request\nUser: {$user->name} ({$user->email})\nAmount: GHS " . number_format($request->amount, 2) . "\nDescription: " . ($request->description ?? 'N/A');
        $subject = "New Deposit Request - Priority Savings Group";
        $notificationService->notifyAdmins($message, $subject);

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit request submitted successfully! It will be reviewed by the admin.');
    }

    /**
     * Approve a deposit (Admin only).
     */
    public function approve(Deposit $deposit)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can approve deposits.');
        }

        if ($deposit->status !== 'pending') {
            return back()->withErrors(['status' => 'This deposit has already been processed.']);
        }

        // Get Priority Bank source
        $priorityBank = SystemRegistry::where('system_id', 'priority_bank')->first();
        
        if (!$priorityBank) {
            return back()->withErrors(['error' => 'Priority Bank source not found.']);
        }

        // Update deposit status
        $deposit->update([
            'status' => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Create income transaction against Priority Bank source
        Transaction::create([
            'user_id' => $deposit->user_id,
            'type' => 'income',
            'category' => 'Deposit',
            'amount' => $deposit->amount,
            'date' => now(),
            'description' => $deposit->description ?? "Deposit from {$deposit->user->name}",
            'external_system_id' => $priorityBank->id,
        ]);

        // Notify user about approval
        $user = $deposit->user;
        if ($user->phone) {
            \App\Jobs\SendNotificationMessage::dispatch('sms', $user->phone, "Your deposit of GHS " . number_format($deposit->amount, 2) . " has been approved and recorded.");
        }
        if ($user->email) {
            \App\Jobs\SendNotificationMessage::dispatch('email', $user->email, "Your deposit of GHS " . number_format($deposit->amount, 2) . " has been approved and recorded.", "Deposit Approved - Priority Savings Group");
        }

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit approved and transaction recorded successfully!');
    }

    /**
     * Reject a deposit (Admin only).
     */
    public function reject(Request $request, Deposit $deposit)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Only administrators can reject deposits.');
        }

        if ($deposit->status !== 'pending') {
            return back()->withErrors(['status' => 'This deposit has already been processed.']);
        }

        $deposit->update([
            'status' => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit rejected.');
    }

    /**
     * Display the specified deposit.
     */
    public function show(Deposit $deposit)
    {
        if (!Auth::user()->isAdmin() && $deposit->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access.');
        }

        return view('deposits.show', compact('deposit'));
    }
}
