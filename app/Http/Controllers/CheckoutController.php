<?php

namespace App\Http\Controllers;

use App\Actions\QueueBuyerOrderUpdate;
use App\Enums\BuyerOrderUpdateType;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CheckoutController extends Controller
{
    public function __construct(
        private QueueBuyerOrderUpdate $queueBuyerOrderUpdate,
    ) {}

    public function fromCart(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['required', Rule::in(['1'])],
            'currency' => ['sometimes', 'string', Rule::in(['idr', 'myr', 'usd'])],
        ]);
        $cart = $request->session()->get('cart', []);
        $checkoutItems = [];

        foreach (array_keys($validated['items']) as $variantId) {
            if (! array_key_exists((string) $variantId, $cart)) {
                throw ValidationException::withMessages([
                    'items' => __('store.cart_item_unavailable'),
                ]);
            }

            $checkoutItems[(int) $variantId] = (int) $cart[$variantId];
        }

        $currencyRates = ProductCurrencyRate::current();
        if ($this->containsDisabledBobuxProducts($checkoutItems, $currencyRates)) {
            throw ValidationException::withMessages([
                'items' => __('store.robux_transactions_disabled'),
            ]);
        }

        $cartCurrency = $this->currency($validated['currency'] ?? $request->session()->get('cart_currency', 'idr'));
        $request->session()->put('cart_currency', $cartCurrency);
        $checkoutCurrencies = collect($checkoutItems)->mapWithKeys(
            fn ($quantity, $variantId): array => [$variantId => $cartCurrency],
        );

        $request->session()->put('checkout', [
            'source' => 'cart',
            'items' => $checkoutItems,
            'currencies' => $checkoutCurrencies->all(),
        ]);

        return redirect()->route('checkout.show');
    }

    public function direct(Request $request): RedirectResponse
    {
        $validatedVariant = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
        ]);
        $variant = ProductVariant::query()
            ->with(['product.gameCategory:id,name', 'product.itemCategory:id,bobux_type,uses_robux'])
            ->whereKey($validatedVariant['variant_id'])
            ->where('status', 'active')
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->firstOrFail();
        $currencyRates = ProductCurrencyRate::current();
        if (
            $variant->product->usesRobuxPricing()
            && ! $currencyRates->bobuxTransactionsEnabled($variant->product->bobuxType())
        ) {
            throw ValidationException::withMessages([
                'variant_id' => __('store.robux_transactions_disabled'),
            ]);
        }

        $purchaseFields = $variant->product->purchaseFields();
        $validated = $request->validate(array_merge([
            'quantity' => ['required', 'integer', 'min:1'],
            'currency' => ['sometimes', 'string', Rule::in(['idr', 'myr', 'usd'])],
            'seller_note' => ['nullable', 'string', 'max:1000'],
        ], $this->purchaseFieldRules($purchaseFields)));

        $quantity = (int) $validated['quantity'];
        $this->validateQuantity($variant, $quantity);

        $purchaseDetails = [];
        if ($purchaseFields !== []) {
            $purchaseDetails['purchase_details'] = $validated['purchase_details'];
        }
        if (filled($validated['seller_note'] ?? null)) {
            $purchaseDetails['seller_note'] = $validated['seller_note'];
        }

        $request->session()->put('checkout', [
            'source' => 'direct',
            'items' => [$variant->id => $quantity],
            'currencies' => [$variant->id => $validated['currency'] ?? 'idr'],
            'details' => [
                $variant->id => Crypt::encryptString(json_encode($purchaseDetails, JSON_THROW_ON_ERROR)),
            ],
        ]);

        return redirect()->route('checkout.show');
    }

    public function show(Request $request): View|RedirectResponse
    {
        $checkout = $request->session()->get('checkout');
        if (! is_array($checkout) || ($checkout['items'] ?? []) === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => __('store.cart_empty'),
            ]);
        }

        $currencyRates = ProductCurrencyRate::current();
        if ($this->containsDisabledBobuxProducts($checkout['items'], $currencyRates)) {
            $request->session()->forget('checkout');

            return redirect()->route('cart.index')->withErrors([
                'cart' => __('store.robux_transactions_disabled'),
            ]);
        }

        $variants = ProductVariant::query()
            ->with('product:id,name,image,description,status,item_category_id')
            ->with('product.itemCategory:id,bobux_type,uses_robux')
            ->whereIn('id', array_keys($checkout['items']))
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        if ($variants->count() !== count($checkout['items'])) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => __('store.cart_item_unavailable'),
            ]);
        }

        foreach ($checkout['items'] as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (
                $variant === null
                || $variant->status !== 'active'
                || $variant->product->status !== 'active'
                || $quantity < $variant->minimum_quantity
                || $quantity > $variant->maximum_quantity
                || $quantity > $variant->stock
            ) {
                $request->session()->forget('checkout');

                return redirect()->route('cart.index')->withErrors([
                    'cart' => __('store.cart_item_unavailable'),
                ]);
            }
        }

        $currencyRates = ProductCurrencyRate::current();
        $items = collect($checkout['items'])->map(function (int $quantity, int|string $variantId) use ($variants, $checkout, $currencyRates): array {
            $variant = $variants->get($variantId);
            $currency = $this->currency($checkout['currencies'][$variantId] ?? 'idr');
            $unitPriceInIdr = $variant->product->priceInIdr((float) $variant->price, $currencyRates);
            $idrSubtotal = $unitPriceInIdr * $quantity;
            $displayUnitPrice = $currencyRates->convertIdrTo($unitPriceInIdr, $currency);
            $displaySubtotal = $displayUnitPrice * $quantity;

            return [
                'variant' => $variant,
                'quantity' => $quantity,
                'subtotal' => $idrSubtotal,
                'displaySubtotal' => $displaySubtotal,
                'currency' => $currency,
                'currencySymbol' => $this->currencySymbol($currency),
            ];
        });
        $totalCurrency = $this->checkoutCurrency($checkout);
        $idrTotal = $items->sum('subtotal');
        $displayTotal = $items->sum('displaySubtotal');
        $totalCurrencySymbol = $this->currencySymbol($totalCurrency);

        $buyer = $request->user();

        return view('checkout.show', [
            'items' => $items,
            'total' => $displayTotal,
            'totalCurrencySymbol' => $totalCurrencySymbol,
            'totalCurrency' => $totalCurrency,
            'checkoutCurrency' => $totalCurrency,
            'customerEmail' => $buyer?->email ?? '',
            'customerPhone' => $buyer?->phone ?? '',
            'storeSetting' => StoreSetting::current(),
            'paymentSettings' => PaymentSetting::query()
                ->where('is_active', true)
                ->where('currency', $totalCurrency)
                ->where(function ($query): void {
                    $query->whereNotNull('name')->orWhereNotNull('bank_name');
                })
                ->orderBy('name')
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['required', 'integer', 'exists:payment_settings,id'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'regex:/^[+0-9][0-9\s().-]{6,39}$/'],
        ], [
            'customer_phone.regex' => __('store.checkout_phone_invalid'),
        ]);
        $checkout = $request->session()->get('checkout');

        if (! is_array($checkout) || ($checkout['items'] ?? []) === []) {
            throw ValidationException::withMessages([
                'cart' => __('store.cart_empty'),
            ]);
        }

        $checkoutCurrency = $this->checkoutCurrency($checkout);
        $currencyRates = ProductCurrencyRate::current();
        $cartDetails = $request->session()->get('cart_details', []);

        $order = DB::transaction(function () use ($request, $validated, $checkout, $cartDetails, $checkoutCurrency, $currencyRates): Order {
            $currencyRates = ProductCurrencyRate::query()
                ->whereKey($currencyRates->id)
                ->lockForUpdate()
                ->firstOrFail();
            $idrPerCurrencyUnit = match ($checkoutCurrency) {
                'myr' => (float) $currencyRates->idr_per_myr,
                'usd' => (float) $currencyRates->idr_per_usd,
                default => 1.0,
            };

            $paymentSetting = PaymentSetting::query()
                ->whereKey($validated['payment_method_id'])
                ->where('is_active', true)
                ->where('currency', $checkoutCurrency)
                ->lockForUpdate()
                ->first();

            if ($paymentSetting === null) {
                throw ValidationException::withMessages([
                    'payment_method_id' => __('store.payment_method_unavailable'),
                ]);
            }

            $variants = ProductVariant::query()
                ->with(['product.itemCategory:id,bobux_type,uses_robux'])
                ->whereIn('id', array_keys($checkout['items']))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($variants->count() !== count($checkout['items'])) {
                throw ValidationException::withMessages([
                    'cart' => __('store.cart_item_unavailable'),
                ]);
            }

            $total = 0.0;
            $paymentAmount = 0.0;
            foreach ($checkout['items'] as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                if (
                    $variant->status !== 'active'
                    || $variant->product->status !== 'active'
                    || ($variant->product->usesRobuxPricing()
                        && ! $currencyRates->bobuxTransactionsEnabled($variant->product->bobuxType()))
                    || $quantity < $variant->minimum_quantity
                    || $quantity > $variant->maximum_quantity
                    || $quantity > $variant->stock
                ) {
                    throw ValidationException::withMessages([
                        'cart' => __('store.cart_item_unavailable'),
                    ]);
                }

                $unitPriceInIdr = $variant->product->priceInIdr((float) $variant->price, $currencyRates);
                $total += $unitPriceInIdr * $quantity;
                $paymentAmount += $currencyRates->convertIdrTo($unitPriceInIdr, $checkoutCurrency) * $quantity;
            }

            $order = Order::query()->create([
                'user_id' => $request->user()?->id,
                'contact_email' => $validated['customer_email'],
                'contact_phone' => $validated['customer_phone'],
                'status' => 'pending',
                'total' => $total,
                'payment_details' => [
                    'type' => $paymentSetting->type,
                    'currency' => $paymentSetting->currency,
                    'idr_per_unit' => $idrPerCurrencyUnit,
                    'payment_amount' => round($paymentAmount, 2),
                    'name' => $paymentSetting->name ?? $paymentSetting->bank_name,
                    'bank_name' => $paymentSetting->bank_name,
                    'account_name' => $paymentSetting->account_name,
                    'account_number' => $paymentSetting->account_number,
                    'qr_code_path' => $paymentSetting->qr_code_path,
                    'instructions' => $paymentSetting->instructions,
                ],
            ]);

            foreach ($checkout['items'] as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                $variant->decrement('stock', $quantity);

                $encryptedDetails = $checkout['source'] === 'direct'
                    ? ($checkout['details'][$variantId] ?? null)
                    : ($cartDetails[$variantId] ?? null);
                $savedDetails = $encryptedDetails === null
                    ? []
                    : json_decode(Crypt::decryptString($encryptedDetails), true, 512, JSON_THROW_ON_ERROR);
                $customerDetails = [
                    'contact_email' => $validated['customer_email'],
                    'contact_phone' => $validated['customer_phone'],
                ];

                if (isset($savedDetails['purchase_details'])) {
                    $customerDetails['purchase_details'] = $savedDetails['purchase_details'];
                }
                if (isset($savedDetails['seller_note'])) {
                    $customerDetails['seller_note'] = $savedDetails['seller_note'];
                }

                $order->items()->create([
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $variant->product->name,
                    'product_description' => $variant->product->description,
                    'variant_name' => $variant->name,
                    'quantity' => $quantity,
                    'unit_price' => $variant->product->priceInIdr((float) $variant->price, $currencyRates),
                    'robux_amount' => $variant->product->usesRobuxPricing()
                        ? (float) $variant->product->bobuxAmount(
                            (float) $variant->price,
                            $variant->bobux_amount === null ? null : (float) $variant->bobux_amount,
                        ) * $quantity
                        : null,
                    'customer_details' => $customerDetails,
                ]);
            }

            return $order;
        });

        if ($checkout['source'] === 'cart') {
            $cart = $request->session()->get('cart', []);
            $cartDetails = $request->session()->get('cart_details', []);
            $cartCurrencies = $request->session()->get('cart_currencies', []);
            foreach (array_keys($checkout['items']) as $variantId) {
                unset($cart[$variantId], $cartDetails[$variantId], $cartCurrencies[$variantId]);
            }
            if ($cart === []) {
                $request->session()->forget(['cart', 'cart_details', 'cart_currencies', 'cart_currency']);
            } else {
                $request->session()->put('cart', $cart);
                $request->session()->put('cart_details', $cartDetails);
                $request->session()->put('cart_currencies', $cartCurrencies);
            }
        }

        $request->session()->forget('checkout');
        if ($order->user_id === null) {
            $guestOrders = $request->session()->get('guest_orders', []);
            $guestOrders[$order->id] = true;
            $request->session()->put('guest_orders', $guestOrders);
        }

        $this->queueBuyerOrderUpdate->handle($order, BuyerOrderUpdateType::PaymentRequired);

        return redirect()->route('orders.show', $order)->with('success', __('store.order_created'));
    }

    /**
     * @param  array<int, array{key: string, label: string, type: string}>  $purchaseFields
     * @return array<string, array<int, string>>
     */
    private function purchaseFieldRules(array $purchaseFields): array
    {
        if ($purchaseFields === []) {
            return ['purchase_details' => ['prohibited']];
        }

        $keys = array_column($purchaseFields, 'key');
        $rules = [
            'purchase_details' => ['required', 'array:'.implode(',', $keys)],
        ];

        foreach ($purchaseFields as $field) {
            $fieldRules = ['required', 'string', 'max:255'];
            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
            }
            $rules['purchase_details.'.$field['key']] = $fieldRules;
        }

        return $rules;
    }

    private function validateQuantity(ProductVariant $variant, int $quantity): void
    {
        if ($quantity < $variant->minimum_quantity || $quantity > $variant->maximum_quantity) {
            throw ValidationException::withMessages([
                'quantity' => __('store.cart_quantity_invalid'),
            ]);
        }

        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => __('store.stock_not_enough'),
            ]);
        }
    }

    private function currency(string $currency): string
    {
        return in_array($currency, ['idr', 'myr', 'usd'], true) ? $currency : 'idr';
    }

    private function currencySymbol(string $currency): string
    {
        return match ($currency) {
            'idr' => 'Rp',
            'myr' => 'RM',
            'usd' => '$',
            default => throw new InvalidArgumentException('Unsupported currency: '.$currency),
        };
    }

    private function checkoutCurrency(array $checkout): string
    {
        $currencies = collect($checkout['items'] ?? [])
            ->map(fn ($quantity, $variantId): string => $this->currency($checkout['currencies'][$variantId] ?? 'idr'))
            ->unique();

        if ($currencies->count() !== 1) {
            throw ValidationException::withMessages([
                'items' => __('store.checkout_currency_mismatch'),
            ]);
        }

        return $currencies->sole();
    }

    /**
     * @param  array<int|string, int>  $items
     */
    private function containsDisabledBobuxProducts(array $items, ProductCurrencyRate $currencyRates): bool
    {
        return ProductVariant::query()
            ->with('product.itemCategory:id,bobux_type,uses_robux')
            ->whereIn('id', array_keys($items))
            ->get()
            ->contains(fn (ProductVariant $variant): bool => $variant->product->usesRobuxPricing()
                && ! $currencyRates->bobuxTransactionsEnabled($variant->product->bobuxType()));
    }
}
