<?php

namespace App\Http\Controllers;

use App\Actions\CancelOrder;
use App\Actions\ProcessApiQrisWebhook;
use App\Actions\QueueBuyerOrderUpdate;
use App\Enums\BuyerOrderUpdateType;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentSetting;
use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Services\ApiQrisClient;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use UnexpectedValueException;

class CheckoutController extends Controller
{
    public function __construct(
        private QueueBuyerOrderUpdate $queueBuyerOrderUpdate,
        private ApiQrisClient $apiQrisClient,
        private CancelOrder $cancelOrder,
        private ProcessApiQrisWebhook $processApiQrisWebhook,
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
        $couponCode = $request->session()->get('checkout_coupon_code');
        $coupon = is_string($couponCode)
            ? Coupon::query()->where('code', $couponCode)->first()
            : null;
        $couponUnavailable = $couponCode !== null
            && (! is_string($couponCode) || $coupon === null || ! $coupon->isAvailable());
        $couponDiscountInIdr = $couponUnavailable || $coupon === null
            ? 0.0
            : $coupon->discountAmountFor($idrTotal);
        $couponDiscount = $currencyRates->convertIdrTo($couponDiscountInIdr, $totalCurrency);
        $displayTotal = max(0, $displayTotal - $couponDiscount);
        $totalCurrencySymbol = $this->currencySymbol($totalCurrency);

        $buyer = $request->user();
        $paymentSettings = PaymentSetting::query()
            ->where('is_active', true)
            ->where('currency', $totalCurrency)
            ->where(function ($query): void {
                $query->whereNotNull('name')->orWhereNotNull('bank_name');
            })
            ->when($totalCurrency !== 'idr', fn ($query) => $query->where('type', '!=', 'qris'))
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('checkout.show', [
            'items' => $items,
            'total' => $displayTotal,
            'subtotal' => $items->sum('displaySubtotal'),
            'coupon' => $couponUnavailable ? null : $coupon,
            'couponCodeInput' => is_string($couponCode) ? $couponCode : '',
            'couponUnavailable' => $couponUnavailable,
            'couponDiscountInIdr' => $couponDiscountInIdr,
            'couponDiscount' => $couponDiscount,
            'totalCurrencySymbol' => $totalCurrencySymbol,
            'totalCurrency' => $totalCurrency,
            'checkoutCurrency' => $totalCurrency,
            'customerEmail' => $buyer?->email ?? '',
            'customerPhone' => $buyer?->phone ?? '',
            'storeSetting' => StoreSetting::current(),
            'paymentSettings' => $paymentSettings,
            'apiQrisUnavailableForCurrency' => $totalCurrency !== 'idr'
                && PaymentSetting::query()
                    ->where('is_active', true)
                    ->where('type', 'qris')
                    ->exists(),
        ]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $checkout = $request->session()->get('checkout');
        if (! is_array($checkout) || ($checkout['items'] ?? []) === []) {
            return redirect()->route('cart.index')->withErrors([
                'cart' => __('store.cart_empty'),
            ]);
        }

        $submittedCouponCode = $request->input('coupon_code');
        if (is_string($submittedCouponCode)) {
            $request->merge(['coupon_code' => trim($submittedCouponCode)]);
        }

        $validated = $request->validate([
            'coupon_code' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
        $couponCode = Str::upper(trim($validated['coupon_code']));
        $coupon = Coupon::query()->where('code', $couponCode)->first();

        if ($coupon === null || ! $coupon->isAvailable()) {
            throw ValidationException::withMessages([
                'coupon_code' => __('store.coupon_unavailable'),
            ]);
        }

        $request->session()->put('checkout_coupon_code', $coupon->code);

        return redirect()->route('checkout.show');
    }

    public function removeCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget('checkout_coupon_code');

        return redirect()->route('checkout.show');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_method_id' => ['nullable', 'integer', 'exists:payment_settings,id'],
            'customer_email' => ['required_without:customer_phone', 'nullable', 'string', 'email', 'max:255'],
            'customer_phone' => ['required_without:customer_email', 'nullable', 'string', 'regex:/^[+0-9][0-9\s().-]{6,39}$/'],
            'buyer_timezone' => ['nullable', 'string', 'timezone'],
        ], [
            'customer_email.required_without' => __('store.checkout_contact_required'),
            'customer_phone.regex' => __('store.checkout_phone_invalid'),
            'customer_phone.required_without' => __('store.checkout_contact_required'),
        ]);
        $customerEmail = $validated['customer_email'] ?? null;
        $customerPhone = $validated['customer_phone'] ?? null;
        $checkout = $request->session()->get('checkout');

        if (! is_array($checkout) || ($checkout['items'] ?? []) === []) {
            throw ValidationException::withMessages([
                'cart' => __('store.cart_empty'),
            ]);
        }

        $checkoutCurrency = $this->checkoutCurrency($checkout);
        $currencyRates = ProductCurrencyRate::current();
        $cartDetails = $request->session()->get('cart_details', []);
        $couponCode = $request->session()->get('checkout_coupon_code');

        $order = DB::transaction(function () use ($request, $validated, $customerEmail, $customerPhone, $checkout, $cartDetails, $checkoutCurrency, $currencyRates, $couponCode): Order {
            $currencyRates = ProductCurrencyRate::query()
                ->whereKey($currencyRates->id)
                ->lockForUpdate()
                ->firstOrFail();
            $idrPerCurrencyUnit = match ($checkoutCurrency) {
                'myr' => (float) $currencyRates->idr_per_myr,
                'usd' => (float) $currencyRates->idr_per_usd,
                default => 1.0,
            };

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

            $coupon = null;
            $couponDiscountInIdr = 0.0;
            $couponDiscount = 0.0;
            if ($couponCode !== null) {
                if (! is_string($couponCode)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => __('store.coupon_unavailable'),
                    ]);
                }

                $coupon = Coupon::query()
                    ->where('code', $couponCode)
                    ->lockForUpdate()
                    ->first();

                if ($coupon === null || ! $coupon->isAvailable()) {
                    throw ValidationException::withMessages([
                        'coupon_code' => __('store.coupon_unavailable'),
                    ]);
                }

                $couponDiscountInIdr = $coupon->discountAmountFor($total);
                $couponDiscount = $currencyRates->convertIdrTo($couponDiscountInIdr, $checkoutCurrency);
                $total -= $couponDiscountInIdr;
                $paymentAmount = max(0, $paymentAmount - $couponDiscount);
            }

            $paymentSetting = null;
            if ($paymentAmount > 0) {
                $paymentSetting = PaymentSetting::query()
                    ->whereKey($validated['payment_method_id'] ?? null)
                    ->where('is_active', true)
                    ->where('currency', $checkoutCurrency)
                    ->lockForUpdate()
                    ->first();

                if ($paymentSetting === null) {
                    throw ValidationException::withMessages([
                        'payment_method_id' => __('store.payment_method_unavailable'),
                    ]);
                }

                if ($paymentSetting->type === 'qris') {
                    if ($checkoutCurrency !== 'idr') {
                        throw ValidationException::withMessages([
                            'payment_method_id' => __('store.apiqris_idr_only'),
                        ]);
                    }

                    if (! $this->apiQrisClient->isConfigured()) {
                        throw ValidationException::withMessages([
                            'payment_method_id' => __('store.apiqris_payment_unavailable'),
                        ]);
                    }

                    $roundedPaymentAmount = (int) round($paymentAmount);
                    if (
                        $roundedPaymentAmount < 1000
                        || $roundedPaymentAmount > 10000000
                        || abs($paymentAmount - $roundedPaymentAmount) > 0.00001
                    ) {
                        throw ValidationException::withMessages([
                            'payment_method_id' => __('store.apiqris_amount_invalid'),
                        ]);
                    }

                    $paymentAmount = $roundedPaymentAmount;
                }
            }

            $orderStatus = $paymentAmount > 0 ? 'pending' : 'payment_confirmed';
            $order = Order::query()->create([
                'user_id' => $request->user()?->id,
                'contact_email' => $customerEmail,
                'contact_phone' => $customerPhone,
                'status' => $orderStatus,
                'total' => round($total, 2),
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'coupon_discount_amount' => $couponDiscountInIdr,
                'coupon_usage_status' => $coupon === null
                    ? null
                    : ($orderStatus === 'payment_confirmed' ? 'redeemed' : 'reserved'),
                'payment_confirmed_at' => $orderStatus === 'payment_confirmed' ? now() : null,
                'payment_details' => [
                    'type' => $paymentSetting?->type ?? 'free',
                    'currency' => $paymentSetting?->currency ?? $checkoutCurrency,
                    'idr_per_unit' => $idrPerCurrencyUnit,
                    'payment_amount' => round($paymentAmount, 2),
                    'name' => $paymentSetting?->name ?? $paymentSetting?->bank_name ?? __('store.no_payment_required'),
                    'bank_name' => $paymentSetting?->bank_name,
                    'account_name' => $paymentSetting?->account_name,
                    'account_number' => $paymentSetting?->account_number,
                    'qr_code_path' => $paymentSetting?->qr_code_path,
                    'instructions' => $paymentSetting?->instructions,
                    'apiqris_required' => $paymentSetting?->type === 'qris',
                    'buyer_timezone' => $validated['buyer_timezone'] ?? config('app.timezone'),
                ],
            ]);

            if ($coupon !== null) {
                $coupon->increment($orderStatus === 'payment_confirmed' ? 'redeemed_uses' : 'reserved_uses');
            }

            foreach ($checkout['items'] as $variantId => $quantity) {
                $variant = $variants->get($variantId);
                $variant->decrement('stock', $quantity);
                $variant->product->deactivateIfOutOfStock();

                $encryptedDetails = $checkout['source'] === 'direct'
                    ? ($checkout['details'][$variantId] ?? null)
                    : ($cartDetails[$variantId] ?? null);
                $savedDetails = $encryptedDetails === null
                    ? []
                    : json_decode(Crypt::decryptString($encryptedDetails), true, 512, JSON_THROW_ON_ERROR);
                $customerDetails = [
                    'contact_email' => $customerEmail,
                    'contact_phone' => $customerPhone,
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

        $apiQrisPaymentConfirmed = false;
        if (($order->payment_details['type'] ?? null) === 'qris') {
            try {
                $invoice = $this->apiQrisClient->createInvoice(
                    (int) $order->payment_details['payment_amount'],
                    $order->publicNumber(),
                    'Meoxstore order '.$order->publicNumber(),
                );
            } catch (ConnectionException|RequestException|UnexpectedValueException|InvalidArgumentException $exception) {
                Log::error('APIQris invoice creation failed.', [
                    'order_id' => $order->publicNumber(),
                    'exception' => $exception::class,
                ]);

                try {
                    $invoice = $this->apiQrisClient->cancelInvoice($order->publicNumber());
                } catch (ConnectionException|RequestException|UnexpectedValueException $cancellationException) {
                    Log::critical('APIQris invoice state could not be reconciled after creation failed.', [
                        'order_id' => $order->publicNumber(),
                        'exception' => $cancellationException::class,
                    ]);

                    return redirect()
                        ->route('orders.show', $order)
                        ->with('warning', __('store.apiqris_payment_initializing'));
                }

                if (($invoice['status'] ?? null) === 'paid') {
                    $this->saveApiQrisInvoice($order, $invoice);
                    $invoice['event'] = 'invoice.paid';
                    $this->processApiQrisWebhook->handle($invoice);
                    $apiQrisPaymentConfirmed = true;
                } elseif (
                    in_array($invoice['status'] ?? null, ['cancelled', 'expired', 'not_found'], true)
                ) {
                    DB::transaction(function () use ($order): void {
                        $lockedOrder = Order::query()
                            ->with('items')
                            ->lockForUpdate()
                            ->findOrFail($order->id);

                        if ($lockedOrder->status === 'pending') {
                            $this->cancelOrder->handle($lockedOrder);
                        }
                    });

                    return redirect()->route('checkout.show')->withErrors([
                        'payment_method_id' => __('store.apiqris_payment_unavailable'),
                    ]);
                } elseif (
                    ($invoice['status'] ?? null) === 'pending'
                    && isset($invoice['qr_image_url'], $invoice['expires_at'])
                ) {
                    // The initial response may have been lost after the provider created the invoice.
                } else {
                    Log::critical('APIQris returned an unresolved invoice state after creation failed.', [
                        'order_id' => $order->publicNumber(),
                        'provider_status' => $invoice['status'] ?? null,
                    ]);

                    return redirect()
                        ->route('orders.show', $order)
                        ->with('warning', __('store.apiqris_payment_initializing'));
                }
            }

            if (! $apiQrisPaymentConfirmed) {
                $this->saveApiQrisInvoice($order, $invoice);
            }

            $order->refresh();
        }

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

        $request->session()->forget(['checkout', 'checkout_coupon_code']);
        if ($order->user_id === null) {
            $guestOrders = $request->session()->get('guest_orders', []);
            $guestOrders[$order->id] = true;
            $request->session()->put('guest_orders', $guestOrders);
        }

        if (! $apiQrisPaymentConfirmed) {
            $this->queueBuyerOrderUpdate->handle(
                $order,
                $order->status === 'payment_confirmed'
                    ? BuyerOrderUpdateType::PaymentConfirmed
                    : BuyerOrderUpdateType::PaymentRequired,
            );
        }

        return redirect()->route('orders.show', $order)->with('success', __('store.order_created'));
    }

    /**
     * @param  array<string, mixed>  $invoice
     */
    private function saveApiQrisInvoice(Order $order, array $invoice): void
    {
        $paymentDetails = $order->payment_details ?? [];
        $paymentDetails['apiqris'] = [
            'invoice_id' => $invoice['id'],
            'amount' => $invoice['amount'],
            'unique_code' => $invoice['unique_code'] ?? ($invoice['total_amount'] - $invoice['amount']),
            'total_amount' => $invoice['total_amount'],
            'qr_image_url' => $invoice['qr_image_url'] ?? null,
            'qris_string' => $invoice['qris_string'] ?? null,
            'expires_at' => $invoice['expires_at'] ?? null,
            'status' => $invoice['status'],
        ];

        $order->update(['payment_details' => $paymentDetails]);
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
