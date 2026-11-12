<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentSettingsController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-settings.index', [
            'paymentSettings' => PaymentSetting::query()
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.payment-settings.form', [
            'paymentSetting' => new PaymentSetting([
                'type' => 'bank_transfer',
                'currency' => 'idr',
                'is_active' => true,
            ]),
            'title' => 'Add payment method',
            'action' => route('admin.payment-settings.store'),
            'method' => 'POST',
            'isCreating' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request, true);
        $qrCodePath = $this->storeQrCode($request);
        $logoPath = $this->storeLogo($request);
        if ($qrCodePath !== null) {
            $validated['qr_code_path'] = $qrCodePath;
        }
        if ($logoPath !== null) {
            $validated['logo_path'] = $logoPath;
        }

        try {
            PaymentSetting::query()->create($validated);
        } catch (QueryException $exception) {
            foreach ([$qrCodePath, $logoPath] as $uploadedPath) {
                if ($uploadedPath !== null) {
                    Storage::disk('public')->delete($uploadedPath);
                }
            }

            throw $exception;
        }

        return redirect()->route('admin.payment-settings.index')->with('success', 'Payment method created.');
    }

    public function edit(PaymentSetting $paymentSetting): View
    {
        return view('admin.payment-settings.form', [
            'paymentSetting' => $paymentSetting,
            'title' => 'Edit payment method',
            'action' => route('admin.payment-settings.update', $paymentSetting),
            'method' => 'PUT',
            'isCreating' => false,
        ]);
    }

    public function update(Request $request, PaymentSetting $paymentSetting): RedirectResponse
    {
        $validated = $this->validatedData($request, false, $paymentSetting);
        $qrCodePath = $this->storeQrCode($request);
        $logoPath = $this->storeLogo($request);
        if ($qrCodePath !== null) {
            $validated['qr_code_path'] = $qrCodePath;
        }
        if ($logoPath !== null) {
            $validated['logo_path'] = $logoPath;
        }

        try {
            $paymentSetting->update($validated);
        } catch (QueryException $exception) {
            foreach ([$qrCodePath, $logoPath] as $uploadedPath) {
                if ($uploadedPath !== null) {
                    Storage::disk('public')->delete($uploadedPath);
                }
            }

            throw $exception;
        }

        return redirect()->route('admin.payment-settings.index')->with('success', 'Payment method updated.');
    }

    public function destroy(PaymentSetting $paymentSetting): RedirectResponse
    {
        // Order snapshots keep this file path, so deleting a payment method must not break old orders.
        $paymentSetting->delete();

        return redirect()->route('admin.payment-settings.index')->with('success', 'Payment method deleted.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', Rule::exists('payment_settings', 'id')],
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
        ]);

        DB::transaction(function () use ($validated): void {
            $paymentSettings = PaymentSetting::query()
                ->whereKey($validated['ids'])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($paymentSettings->count() !== count($validated['ids'])) {
                throw ValidationException::withMessages([
                    'ids' => 'One or more selected payment methods no longer exist.',
                ]);
            }

            if ($validated['action'] === 'delete') {
                foreach ($paymentSettings as $paymentSetting) {
                    $paymentSetting->delete();
                }

                return;
            }

            $isActive = $validated['action'] === 'activate';
            foreach ($paymentSettings as $paymentSetting) {
                $paymentSetting->update(['is_active' => $isActive]);
            }
        });

        $message = match ($validated['action']) {
            'activate' => 'Selected payment methods activated.',
            'deactivate' => 'Selected payment methods deactivated.',
            default => 'Selected payment methods deleted.',
        };

        return redirect()->route('admin.payment-settings.index')->with('success', $message);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, bool $defaultActive, ?PaymentSetting $paymentSetting = null): array
    {
        $validated = $request->validate([
            'type' => ['sometimes', 'string', Rule::in(['bank_transfer', 'e_wallet', 'qris', 'other'])],
            'currency' => ['required', 'string', Rule::in(['idr', 'myr', 'usd'])],
            'name' => ['required', 'string', 'max:100'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'account_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'qr_code' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'instructions' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        unset($validated['logo'], $validated['qr_code']);
        if (! $request->exists('bank_name')) {
            if ($paymentSetting === null) {
                $validated['bank_name'] = $validated['name'];
            } else {
                unset($validated['bank_name']);
            }
        }
        $validated['type'] = $request->hasFile('qr_code')
            ? 'qris'
            : ($validated['type'] ?? $paymentSetting?->type ?? 'bank_transfer');

        if (
            $validated['type'] === 'qris'
            && ! $request->hasFile('qr_code')
            && $paymentSetting?->qr_code_path === null
        ) {
            throw ValidationException::withMessages([
                'qr_code' => 'Upload a QR image for QRIS payment methods.',
            ]);
        }

        $validated['is_active'] = $request->has('is_active_present')
            ? $request->boolean('is_active')
            : $defaultActive;

        return $validated;
    }

    private function storeQrCode(Request $request): ?string
    {
        if (! $request->hasFile('qr_code')) {
            return null;
        }

        $path = $request->file('qr_code')->store('payment-qr-codes', 'public');
        if ($path === false) {
            throw new \RuntimeException('Unable to store the payment QR image.');
        }

        return $path;
    }

    private function storeLogo(Request $request): ?string
    {
        if (! $request->hasFile('logo')) {
            return null;
        }

        $path = $request->file('logo')->store('payment-method-logos', 'public');
        if ($path === false) {
            throw new \RuntimeException('Unable to store the payment method logo.');
        }

        return $path;
    }
}
