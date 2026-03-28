<?php

namespace App\Http\Controllers;

/**
 * @uses \Illuminate\Foundation\Auth\Access\AuthorizesRequests
 */
use App\Models\Category;
use App\Models\SystemRegistry;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Transaction::query();

        // Admins see all transactions (or filter by user_id for CEO personal view); regular users see only their own
        if (! auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($request->filled('user_id')) {
            // Admin filtering to a specific user (e.g. CEO's own transactions)
            $query->where('user_id', $request->user_id);
        }

        $query
            ->when($request->type, function ($q) use ($request) {
                return $q->where('type', $request->type);
            })
            ->when($request->start_date, function ($q) use ($request) {
                return $q->where('date', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                return $q->where('date', '<=', $request->end_date);
            });

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            if ($term !== '') {
                $like = '%'.addcslashes($term, '%_\\').'%';
                $driver = DB::connection()->getDriverName();
                $amountCast = $driver === 'sqlite' ? 'CAST(amount AS TEXT)' : 'CAST(amount AS CHAR(50))';

                $query->where(function ($q) use ($like, $amountCast) {
                    $q->where('description', 'like', $like)
                        ->orWhere('category', 'like', $like)
                        ->orWhere('notes', 'like', $like)
                        ->orWhere('type', 'like', $like)
                        ->orWhere('external_transaction_id', 'like', $like)
                        ->orWhereRaw("{$amountCast} LIKE ?", [$like])
                        ->orWhere('date', 'like', $like)
                        ->orWhereHas('user', function ($uq) use ($like) {
                            $uq->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('phone', 'like', $like);
                        })
                        ->orWhereHas('externalSystem', function ($sq) use ($like) {
                            $sq->where('name', 'like', $like)
                                ->orWhere('system_id', 'like', $like);
                        })
                        ->orWhereHas('depositSaving', function ($dq) use ($like) {
                            $dq->where('notes', 'like', $like)
                                ->orWhere('reference', 'like', $like);
                        });
                });
            }
        }

        $transactions = $query
            ->with(['user', 'externalSystem', 'depositSaving'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(100)
            ->withQueryString();

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');

        return view('transactions.create', [
            'categories' => $this->formCategoryOptions(),
            'systems' => $systems,
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
                'notes' => 'nullable|string|max:65535',
                'external_system_id' => 'nullable|exists:systems_registry,id',
                'user_id' => 'nullable|exists:users,id',
                'notify_user_on_create' => 'nullable|boolean',
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
                'external_system_id' => $validated['external_system_id'] ?? null,
            ]);

            // Optional notification (controlled by checkbox in the modal).
            if ($request->boolean('notify_user_on_create')) {
                try {
                    $ownerUserId = (int) ($validated['user_id'] ?? auth()->id());
                    $user = \App\Models\User::find($ownerUserId);
                    if ($user) {
                        $userNotificationService = new \App\Services\UserNotificationService;
                        $userNotificationService->notifyTransactionCreated(
                            $user,
                            $validated['type'],
                            $validated['amount'],
                            $category,
                            $validated['description']
                        );
                    }
                } catch (\Exception $e) {
                    // Log notification error but don't fail the transaction.
                    \Illuminate\Support\Facades\Log::warning('Failed to send transaction notification', [
                        'owner_user_id' => $validated['user_id'] ?? auth()->id(),
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Handle AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction added successfully!',
                ]);
            }

            return redirect()->route('transactions.index')
                ->with('success', 'Transaction added successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $e->errors(),
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
                    'message' => 'An error occurred while saving the transaction. Please try again.',
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

        $type = old('type', $transaction->type);
        $category = old('category', $transaction->category);

        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => $this->formCategoryOptions($type, $category),
        ]);
    }

    /**
     * Category names for create/edit selects (same source as DB + optional legacy/custom value).
     *
     * @param  string|null  $ensureType  income|expense — include $ensureCategory in this list if missing
     * @param  string|null  $ensureCategory  e.g. legacy import label not in categories table
     * @return array{income: array<int, string>, expense: array<int, string>}
     */
    private function formCategoryOptions(?string $ensureType = null, ?string $ensureCategory = null): array
    {
        $categories = Category::query()->orderBy('name')->get()->groupBy(function (Category $category) {
            if ($category->type === 'both') {
                return 'both';
            }

            return $category->type;
        })->map(function ($group) {
            return $group->pluck('name')->toArray();
        })->toArray();

        $categories['income'] = array_merge(
            $categories['income'] ?? [],
            $categories['both'] ?? []
        );
        $categories['expense'] = array_merge(
            $categories['expense'] ?? [],
            $categories['both'] ?? []
        );

        if ($ensureType !== null && $ensureCategory !== null && $ensureCategory !== ''
            && in_array($ensureType, ['income', 'expense'], true)) {
            if (! in_array($ensureCategory, $categories[$ensureType] ?? [], true)) {
                $categories[$ensureType] ??= [];
                array_unshift($categories[$ensureType], $ensureCategory);
            }
        }

        return $categories;
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
            'notes' => 'nullable|string|max:65535',
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
