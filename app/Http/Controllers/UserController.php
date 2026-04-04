<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use App\Services\UserNotificationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Middleware is applied in routes/web.php

    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('account_id', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query
            ->with('ownedSystems')
            ->latest()
            ->paginate(50);

        $this->hydrateUserNetBalancesForPage($users);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'account_id' => ['nullable', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'preferred_currency' => ['nullable', 'string', 'max:10'],
            'send_welcome_message' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'account_id' => $validated['account_id'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'preferred_currency' => $validated['preferred_currency'] ?? 'GHS',
            'status' => 'approved', // Admin-created users are auto-approved
        ]);

        if ($request->boolean('send_welcome_message')) {
            app(UserNotificationService::class)->sendWelcomeMessage($user, 'all');
        }

        $message = 'User created successfully.';
        if ($request->boolean('send_welcome_message')) {
            $message .= ' Welcome message sent via all channels.';
        }

        return redirect()->route('admin.users.index')
            ->with('success', $message);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Batch-compute net balance for each user on the current index page so the Balance column matches
     * User::net_balance / profile (same rules as savings_balance minus loan_balance accessors).
     */
    protected function hydrateUserNetBalancesForPage(LengthAwarePaginator $paginator): void
    {
        $collection = $paginator->getCollection();
        if ($collection->isEmpty()) {
            return;
        }

        $ids = $collection->pluck('id')->all();

        $savingsByUser = Saving::query()
            ->whereIn('user_id', $ids)
            ->where('status', 'successful')
            ->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
            ->pluck('total', 'user_id');

        $incomeByUser = Transaction::query()
            ->whereIn('user_id', $ids)
            ->where('type', 'income')
            ->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
            ->pluck('total', 'user_id');

        $loanRemainingByUser = Loan::query()
            ->whereIn('user_id', $ids)
            ->where('is_group_loan', true)
            ->where('status', 'borrowed')
            ->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(remaining_balance), 0) as total')
            ->pluck('total', 'user_id');

        $expenseByUser = Transaction::query()
            ->whereIn('user_id', $ids)
            ->where('type', 'expense')
            ->groupBy('user_id')
            ->selectRaw('user_id, COALESCE(SUM(amount), 0) as total')
            ->pluck('total', 'user_id');

        foreach ($collection as $user) {
            $id = $user->id;
            $savingsSide = (float) ($savingsByUser[$id] ?? 0) + (float) ($incomeByUser[$id] ?? 0);
            $loanSide = (float) ($loanRemainingByUser[$id] ?? 0) + (float) ($expenseByUser[$id] ?? 0);
            $user->setAttribute('aggregated_net_balance', round($savingsSide - $loanSide, 2));
        }
    }

    /**
     * View statement for a user (transactions table; header uses full account balances).
     */
    public function statement(Request $request, User $user)
    {
        $startDate = $request->has('start_date') ? $request->date('start_date') : null;
        $endDate = $request->has('end_date') ? $request->date('end_date') : null;
        $data = $this->getStatementData($user, $startDate, $endDate);

        return view('admin.users.statement', [
            'user' => $user,
            'entries' => $data['entries'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalCredits' => $data['totalCredits'],
            'totalDebits' => $data['totalDebits'],
            'netBalance' => $data['netBalance'],
        ]);
    }

    /**
     * Get statement data (entries and totals) for a user and optional date range.
     */
    protected function getStatementData(User $user, $startDate, $endDate): array
    {
        $transactions = $user->transactions()
            ->when($startDate, fn ($q) => $q->where('date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('date', '<=', $endDate))
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($t) => (object) [
                'date' => $t->date,
                'type' => $t->type === 'income' ? 'income' : 'expense',
                'source' => 'transaction',
                'description' => $t->description ?: $t->category,
                'category' => $t->category,
                'amount' => (float) $t->amount,
                'reference' => $t->id,
                'transaction_id' => $t->id,
                'has_notes' => filled($t->notes),
            ]);

        // Statement uses transactions table only to avoid double counting
        // where a savings deposit is also represented as a transaction.
        $entries = $transactions
            ->sortByDesc(fn ($e) => $e->date->format('Y-m-d').'-t-'.($e->reference ?? 0))
            ->values();

        $totalCredits = $entries->where('type', 'income')->sum('amount');
        $totalDebits = $entries->where('type', 'expense')->sum('amount');
        $netBalance = $totalCredits - $totalDebits;

        return [
            'entries' => $entries,
            'totalCredits' => $totalCredits,
            'totalDebits' => $totalDebits,
            'netBalance' => $netBalance,
        ];
    }

    /**
     * Download statement as PDF (A4).
     */
    public function statementPdf(Request $request, User $user)
    {
        $startDate = $request->has('start_date') ? $request->date('start_date') : null;
        $endDate = $request->has('end_date') ? $request->date('end_date') : null;
        $data = $this->getStatementData($user, $startDate, $endDate);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.users.statement-pdf', [
            'user' => $user,
            'entries' => $data['entries'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalCredits' => $data['totalCredits'],
            'totalDebits' => $data['totalDebits'],
            'netBalance' => $data['netBalance'],
        ])->setPaper('a4', 'portrait');

        $filename = 'statement-'.$user->name.'-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Send statement as email with PDF attachment to the user.
     */
    public function sendStatementEmail(Request $request, User $user)
    {
        $startDate = $request->has('start_date') ? $request->date('start_date') : null;
        $endDate = $request->has('end_date') ? $request->date('end_date') : null;
        $data = $this->getStatementData($user, $startDate, $endDate);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.users.statement-pdf', [
            'user' => $user,
            'entries' => $data['entries'],
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalCredits' => $data['totalCredits'],
            'totalDebits' => $data['totalDebits'],
            'netBalance' => $data['netBalance'],
        ])->setPaper('a4', 'portrait');

        $pdfContent = $pdf->output();
        $filename = 'statement-'.preg_replace('/[^a-z0-9_-]/i', '-', $user->name).'-'.now()->format('Y-m-d').'.pdf';

        $body = "Dear {$user->name},\n\nPlease find your Priority Bank statement attached to this email.\n\nBest regards,\nPriority Bank";
        \Illuminate\Support\Facades\Mail::raw($body, function ($message) use ($user, $pdfContent, $filename) {
            $message->to($user->email)
                ->subject('Your Priority Bank Statement')
                ->attachData($pdfContent, $filename, ['mime' => 'application/pdf']);
        });

        return redirect()->back()->with('success', 'Statement has been sent to '.$user->email.'.');
    }

    /**
     * Send welcome message to the user via selected channel(s).
     */
    public function sendWelcomeMessage(Request $request, User $user, UserNotificationService $notificationService)
    {
        $request->validate(['channel' => ['required', 'string', Rule::in(['all', 'gekychat', 'sms', 'email', 'whatsapp'])]]);
        $notificationService->sendWelcomeMessage($user, $request->channel);
        $channelLabel = match ($request->channel) {
            'all' => 'All channels',
            'gekychat' => 'GekyChat',
            'sms' => 'SMS',
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
        };

        return redirect()->back()->with('success', "Welcome message sent to {$user->name} via {$channelLabel}.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'account_id' => ['nullable', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'preferred_currency' => ['nullable', 'string', 'max:10'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->account_id = $validated['account_id'] ?? null;
        $user->role = $validated['role'];
        $user->preferred_currency = $validated['preferred_currency'] ?? 'GHS';

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete(); // soft delete: user is hidden from listings

        return redirect()->route('admin.users.index')
            ->with('success', 'User has been hidden from the system.');
    }

    /**
     * Approve a pending user.
     */
    public function approve(User $user)
    {
        $user->status = 'approved';
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User approved successfully.');
    }

    /**
     * Reject a pending user.
     */
    public function reject(User $user)
    {
        $user->status = 'rejected';
        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'User rejected successfully.');
    }

    /**
     * Impersonate a user.
     */
    public function impersonate(User $user)
    {
        // Prevent impersonating yourself
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot impersonate yourself.');
        }

        // Store the original admin user ID in session
        session(['impersonating' => auth()->id()]);

        // Log in as the target user
        auth()->login($user);

        return redirect()->route('dashboard')
            ->with('success', "You are now impersonating {$user->name}.");
    }

    /**
     * Stop impersonating and return to admin account.
     */
    public function stopImpersonating()
    {
        if (! session()->has('impersonating')) {
            return redirect()->route('dashboard')
                ->with('error', 'You are not currently impersonating anyone.');
        }

        $adminId = session('impersonating');
        session()->forget('impersonating');

        $admin = User::findOrFail($adminId);
        auth()->login($admin);

        return redirect()->route('admin.users.index')
            ->with('success', 'You have stopped impersonating.');
    }
}
