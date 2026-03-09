<?php

namespace App\Http\Controllers;
/**
 * @uses \Illuminate\Foundation\Auth\Access\AuthorizesRequests
 */
use App\Models\Transaction;
use App\Models\SystemRegistry;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Transaction::query();
        
        // Admins see all transactions, regular users see only their own
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        
        $transactions = $query
            ->when(request('type'), function ($q) {
                return $q->where('type', request('type'));
            })
            ->when(request('start_date'), function ($q) {
                return $q->where('date', '>=', request('start_date'));
            })
            ->when(request('end_date'), function ($q) {
                return $q->where('date', '<=', request('end_date'));
            })
            ->with(['user', 'externalSystem', 'depositSaving'])
            ->latest()
            ->paginate(50);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');
        $categories = \App\Models\Category::all()->groupBy(function($category) {
            if ($category->type === 'both') {
                return 'both';
            }
            return $category->type;
        })->map(function($group) {
            return $group->pluck('name')->toArray();
        })->toArray();
        
        // Ensure both income and expense arrays exist
        $categories['income'] = array_merge(
            $categories['income'] ?? [],
            $categories['both'] ?? []
        );
        $categories['expense'] = array_merge(
            $categories['expense'] ?? [],
            $categories['both'] ?? []
        );
        
        return view('transactions.create', [
            'categories' => $categories,
            'systems' => $systems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:income,expense',
                'category' => 'required|string|max:255',
                'amount' => 'required|numeric|min:0.01',
                'date' => 'required|date',
                'description' => 'nullable|string',
                'notes' => 'nullable|string|max:1000',
                'external_system_id' => 'nullable|exists:systems_registry,id',
                'user_id' => 'nullable|exists:users,id'
            ]);

            // Get Priority Bank source
            $priorityBank = SystemRegistry::where('system_id', 'priority_bank')->first();
            $isPriorityBank = $priorityBank && $validated['external_system_id'] == $priorityBank->id;
            
            // If Priority Bank transaction and admin is creating it, set category to loan/savings
            $category = $validated['category'];
            if ($isPriorityBank && auth()->user()->isAdmin() && isset($validated['user_id'])) {
                // Override category based on type for Priority Bank transactions
                $category = $validated['type'] === 'expense' ? 'loan' : 'savings';
            }

            $transaction = Transaction::create([
                'user_id' => $validated['user_id'] ?? auth()->id(),
                'type' => $validated['type'],
                'category' => $category,
                'amount' => $validated['amount'],
                'date' => $validated['date'],
                'description' => $validated['description'],
                'notes' => $validated['notes'] ?? null,
                'external_system_id' => $validated['external_system_id'] ?? null
            ]);

            // Notify user if admin created transaction for another user
            if (auth()->user()->isAdmin() && isset($validated['user_id']) && $validated['user_id'] != auth()->id()) {
                try {
                    $user = \App\Models\User::find($validated['user_id']);
                    if ($user) {
                        $userNotificationService = new \App\Services\UserNotificationService();
                        $userNotificationService->notifyTransactionCreated(
                            $user,
                            $validated['type'],
                            $validated['amount'],
                            $category,
                            $validated['description']
                        );
                    }
                } catch (\Exception $e) {
                    // Log notification error but don't fail the transaction
                    \Illuminate\Support\Facades\Log::warning('Failed to send transaction notification', [
                        'user_id' => $validated['user_id'],
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction added successfully!'
                ]);
            }

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            // Handle any other exceptions
            \Illuminate\Support\Facades\Log::error('Transaction store failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the transaction. Please try again.'
                ], 500);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while saving the transaction. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => [
                'income' => ['Salary', 'Bonus', 'Freelance', 'Investment'],
                'expense' => ['Food', 'Transport', 'Housing', 'Entertainment']
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }
}