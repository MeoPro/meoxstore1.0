<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    private const FILTERABLE_ORDER_STATUSES = [
        'pending',
        'payment_submitted',
        'payment_confirmed',
        'completed',
    ];

    public function edit(Request $request): View
    {
        $userId = $request->user()->id;
        $validated = $request->validate([
            'status' => ['sometimes', 'string', Rule::in(self::FILTERABLE_ORDER_STATUSES)],
        ]);
        $selectedStatus = $validated['status'] ?? null;

        $orders = Order::query()
            ->where('user_id', $userId)
            ->when($selectedStatus !== null, fn ($query) => $query->where('status', $selectedStatus))
            ->withCount('items')
            ->latest()
            ->paginate(5)
            ->withQueryString();
        $statusCounts = Order::query()
            ->where('user_id', $userId)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('profile', [
            'user' => $request->user(),
            'orders' => $orders,
            'statusCounts' => $statusCounts,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function settings(Request $request): View
    {
        return view('profile.settings', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email', ''))),
            'phone' => trim((string) $request->input('phone', '')),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($request->user()),
            ],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $emailChanged = $user->email !== $validated['email'];

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?: null;

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        if (! empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return back()->with('success', __('store.profile_updated'));
    }
}
