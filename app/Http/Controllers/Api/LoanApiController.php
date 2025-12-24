<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Income;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for managing friendly loans.
 */
class LoanApiController extends Controller
{
    /**
     * List loans for the authenticated user. Supports filtering by status.
     */
    public function index(Request $request)
    {
        $query = Loan::where('user_id', Auth::id());
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Create a new loan.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'borrower_name' => 'required|string|max:255',
            'borrower_phone' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'date_given' => 'required|date',
            'expected_return_date' => 'nullable|date|after_or_equal:date_given',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);
        $data['user_id'] = Auth::id();
        $data['status'] = 'borrowed';
        $data['returned_amount'] = 0;
        $loan = Loan::create($data);
        return response()->json($loan, 201);
    }

    /**
     * Mark a loan as returned.
     */
    public function markReturned(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }
        $data = $request->validate([
            'returned_amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
        ]);
        $loan->status = 'returned';
        $loan->returned_amount = $data['returned_amount'];
        $loan->notes = $loan->notes . ' | Returned: ' . $data['returned_amount'];
        $loan->save();
        // Create income entry for loan return
        Income::create([
            'user_id' => $loan->user_id,
            'income_category_id' => null,
            'account_id' => $data['account_id'],
            'amount' => $data['returned_amount'],
            'date' => $data['date'],
            'channel' => $data['channel'],
            'notes' => $data['notes'] ?? 'Loan return from ' . $loan->borrower_name,
        ]);
        return response()->json($loan);
    }

    /**
     * Mark a loan as lost.
     */
    public function markLost(Request $request, Loan $loan)
    {
        if ($loan->user_id !== Auth::id()) {
            abort(403);
        }
        $data = $request->validate([
            'date' => 'required|date',
            'account_id' => 'required|exists:accounts,id',
            'channel' => 'required|in:bank,momo,cash,other',
            'notes' => 'nullable|string',
        ]);
        $remaining = $loan->amount - $loan->returned_amount;
        $loan->status = 'lost';
        $loan->notes = $loan->notes . ' | Lost';
        $loan->save();
        if ($remaining > 0) {
            Expense::create([
                'user_id' => $loan->user_id,
                'expense_category_id' => null,
                'account_id' => $data['account_id'],
                'amount' => $remaining,
                'date' => $data['date'],
                'channel' => $data['channel'],
                'notes' => $data['notes'] ?? 'Loan loss for ' . $loan->borrower_name,
            ]);
        }
        return response()->json($loan);
    }
}
