<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserNotificationService;
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
                  ->orWhere('phone', 'like', "%{$search}%");
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

        $users = $query->latest()->paginate(15);

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
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'preferred_currency' => ['nullable', 'string', 'max:10'],
            'send_welcome_message' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
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
     * View statement for a user (transactions + savings, date-ordered).
     */
    public function statement(Request $request, User $user)
    {
        $startDate = $request->has('start_date') ? $request->date('start_date') : null;
        $endDate = $request->has('end_date') ? $request->date('end_date') : null;

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
            ]);

        $savings = $user->savings()
            ->when($startDate, fn ($q) => $q->where('deposit_date', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->where('deposit_date', '<=', $endDate))
            ->orderBy('deposit_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($s) => (object) [
                'date' => $s->deposit_date,
                'type' => 'income',
                'source' => 'savings',
                'description' => 'Savings deposit' . ($s->reference ? " ({$s->reference})" : ''),
                'category' => 'Savings',
                'amount' => (float) $s->amount,
                'reference' => $s->reference,
                'status' => $s->status,
            ]);

        $entries = $transactions->concat($savings)
            ->sortByDesc(fn ($e) => $e->date->format('Y-m-d') . '-' . ($e->source === 'savings' ? 's' : 't') . '-' . ($e->reference ?? 0))
            ->values();

        return view('admin.users.statement', compact('user', 'entries', 'startDate', 'endDate'));
    }

    /**
     * Send welcome message to the user via selected channel(s).
     */
    public function sendWelcomeMessage(Request $request, User $user, UserNotificationService $notificationService)
    {
        $request->validate(['channel' => ['required', 'string', Rule::in(['all', 'gekychat', 'sms', 'email', 'whatsapp'])]]);
        $notificationService->sendWelcomeMessage($user, $request->channel);
        $channelLabel = match($request->channel) {
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
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'user'])],
            'preferred_currency' => ['nullable', 'string', 'max:10'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        $user->role = $validated['role'];
        $user->preferred_currency = $validated['preferred_currency'] ?? 'GHS';

        if (!empty($validated['password'])) {
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

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
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
        if (!session()->has('impersonating')) {
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
