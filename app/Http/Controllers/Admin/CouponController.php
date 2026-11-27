<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon([
                'discount_type' => 'percent',
                'discount_value' => '10.00',
                'stock' => 1,
                'is_active' => true,
            ]),
            'title' => 'Add coupon',
            'action' => route('admin.coupons.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::query()->create($this->validatedData($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon,
            'title' => 'Edit coupon',
            'action' => route('admin.coupons.update', $coupon),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $validated = $this->validatedData($request, $coupon);

        DB::transaction(function () use ($coupon, $validated): void {
            $lockedCoupon = Coupon::query()->lockForUpdate()->findOrFail($coupon->id);
            $lockedCoupon->update($validated);
        });

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function toggleStatus(Coupon $coupon): RedirectResponse
    {
        DB::transaction(function () use ($coupon): void {
            $lockedCoupon = Coupon::query()->lockForUpdate()->findOrFail($coupon->id);
            $lockedCoupon->update(['is_active' => ! $lockedCoupon->is_active]);
        });

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon status updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Coupon $coupon = null): array
    {
        $code = $request->input('code');
        if (is_string($code)) {
            $request->merge(['code' => Str::upper(trim($code))]);
        }

        $discountType = $request->input('discount_type');
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:40',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount_value' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                Rule::when($discountType === 'percent', ['max:100'], ['max:9999999999.99']),
            ],
            'stock' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
