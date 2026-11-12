<?php

namespace App\Http\Controllers;

use App\Models\ProductCurrencyRate;
use App\Models\ProductVariant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = $request->session()->get('cart', []);
        $variants = ProductVariant::query()
            ->with([
                'product:id,name,image,status,game_category_id,item_category_id,purchase_fields',
                'product.gameCategory:id,name',
                'product.itemCategory:id,bobux_type,uses_robux',
            ])
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');
        $cartDetails = $request->session()->get('cart_details', []);
        $cartCurrencies = $request->session()->get('cart_currencies', []);
        $currency = $this->currency($request->query('currency', $request->session()->get(
            'cart_currency',
            $cartCurrencies[array_key_first($cart)] ?? 'idr',
        )));
        if ($request->query('currency') !== null) {
            $request->session()->put('cart_currency', $currency);
        }
        $currencyRates = ProductCurrencyRate::current();
        $items = collect($cart)->map(function (int $quantity, string $variantId) use ($variants, $cartDetails, $currency, $currencyRates): array {
            $variant = $variants->get($variantId);
            $isAvailable = $variant !== null
                && $variant->status === 'active'
                && $variant->product->status === 'active'
                && (! $variant->product->usesRobuxPricing()
                    || $currencyRates->bobuxTransactionsEnabled($variant->product->bobuxType()))
                && $variant->stock >= $quantity;
            $isRobuxUnavailable = $variant !== null
                && $variant->product->usesRobuxPricing()
                && ! $currencyRates->bobuxTransactionsEnabled($variant->product->bobuxType());
            $details = $this->decryptDetails($cartDetails[$variantId] ?? null);
            $purchaseFields = $variant?->product->purchaseFields() ?? [];

            $unitPrice = $variant === null
                ? 0
                : $variant->product->priceInIdr((float) $variant->price, $currencyRates);
            $subtotal = $unitPrice * $quantity;
            [$displayUnitPrice, $currencySymbol] = $this->convertPrice($unitPrice, $currency, $currencyRates);
            $displaySubtotal = $displayUnitPrice * $quantity;

            return [
                'variant' => $variant,
                'quantity' => $quantity,
                'available' => $isAvailable,
                'robuxUnavailable' => $isRobuxUnavailable,
                'displayUnitPrice' => $displayUnitPrice,
                'subtotal' => $subtotal,
                'displaySubtotal' => $displaySubtotal,
                'details' => $details,
                'purchaseFields' => $purchaseFields,
                'currency' => $currency,
                'currencySymbol' => $currencySymbol,
            ];
        });
        $displayTotal = $items->sum('displaySubtotal');
        $currencySymbol = $this->currencySymbol($currency);

        return view('cart.index', [
            'items' => $items,
            'total' => $displayTotal,
            'currency' => $currency,
            'currencySymbol' => $currencySymbol,
            'hasUnavailableItems' => $items->contains(fn (array $item): bool => ! $item['available']),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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
        $cart = $request->session()->get('cart', []);
        $quantity = (int) ($cart[$variant->id] ?? 0) + (int) $validated['quantity'];

        $this->validateQuantity($variant, $quantity);

        $customerDetails = [];
        if ($purchaseFields !== []) {
            $customerDetails['purchase_details'] = $validated['purchase_details'];
        }
        if (filled($validated['seller_note'] ?? null)) {
            $customerDetails['seller_note'] = $validated['seller_note'];
        }
        $cart[$variant->id] = $quantity;
        $request->session()->put('cart', $cart);
        $cartDetails = $request->session()->get('cart_details', []);
        $cartDetails[$variant->id] = Crypt::encryptString(json_encode($customerDetails, JSON_THROW_ON_ERROR));
        $request->session()->put('cart_details', $cartDetails);
        $cartCurrencies = $request->session()->get('cart_currencies', []);
        $cartCurrencies[$variant->id] = $validated['currency'] ?? 'idr';
        $request->session()->put('cart_currencies', $cartCurrencies);
        $request->session()->put('cart_currency', $validated['currency'] ?? 'idr');

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('store.cart_added'),
                'cart_count' => array_sum($cart),
            ], 201);
        }

        return redirect()->route('cart.index')->with('success', __('store.cart_added'));
    }

    public function update(Request $request, ProductVariant $variant): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = $request->session()->get('cart', []);
        abort_unless(array_key_exists($variant->id, $cart), 404);
        $this->validateQuantity($variant, (int) $validated['quantity']);

        $cart[$variant->id] = (int) $validated['quantity'];
        $request->session()->put('cart', $cart);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('store.cart_updated'),
                'quantity' => $cart[$variant->id],
            ]);
        }

        return redirect()->route('cart.index')->with('success', __('store.cart_updated'));
    }

    public function updateDetails(Request $request, ProductVariant $variant): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        abort_unless(array_key_exists($variant->id, $cart), 404);

        $variant->load('product.gameCategory');
        $cartDetails = $request->session()->get('cart_details', []);
        $existingDetails = $this->decryptDetails($cartDetails[$variant->id] ?? null);
        $cartCurrencies = $request->session()->get('cart_currencies', []);
        if (! isset($cartCurrencies[$variant->id]) && isset($existingDetails['currency'])) {
            $cartCurrencies[$variant->id] = $existingDetails['currency'];
        }
        $purchaseFields = $variant->product->purchaseFields();
        $existingPurchaseDetails = $existingDetails['purchase_details']
            ?? Arr::only($existingDetails, array_column($purchaseFields, 'key'));
        $purchaseRules = $this->purchaseFieldRules($purchaseFields, $existingPurchaseDetails);
        foreach ($purchaseFields as $field) {
            if ($field['type'] === 'password' && filled($existingPurchaseDetails[$field['key']] ?? null)) {
                $purchaseRules['purchase_details.'.$field['key']][0] = 'nullable';
            }
        }

        $validated = $request->validate(array_merge($purchaseRules, [
            'seller_note' => ['nullable', 'string', 'max:1000'],
        ]));

        $updatedDetails = [];

        if ($purchaseFields !== []) {
            $updatedPurchaseDetails = $existingPurchaseDetails;
            foreach ($purchaseFields as $field) {
                $fieldKey = $field['key'];
                if (filled($validated['purchase_details'][$fieldKey] ?? null)) {
                    $updatedPurchaseDetails[$fieldKey] = $validated['purchase_details'][$fieldKey];
                }
            }

            $updatedDetails['purchase_details'] = Arr::only(
                $updatedPurchaseDetails,
                array_column($purchaseFields, 'key'),
            );
        }
        $sellerNote = $validated['seller_note'] ?? null;
        if (filled($sellerNote)) {
            $updatedDetails['seller_note'] = $sellerNote;
        }

        $cartDetails[$variant->id] = Crypt::encryptString(json_encode($updatedDetails, JSON_THROW_ON_ERROR));
        $request->session()->put('cart_details', $cartDetails);
        if (isset($cartCurrencies[$variant->id])) {
            $request->session()->put('cart_currencies', $cartCurrencies);
        }

        return redirect()->route('cart.index')->with('success', __('store.cart_details_updated'));
    }

    public function destroy(Request $request, int $variantId): RedirectResponse
    {
        $cart = $request->session()->get('cart', []);
        $cartDetails = $request->session()->get('cart_details', []);
        $cartCurrencies = $request->session()->get('cart_currencies', []);
        $cartCurrency = $request->session()->get('cart_currency');
        unset($cart[$variantId]);
        unset($cartDetails[$variantId]);
        unset($cartCurrencies[$variantId]);
        $request->session()->put('cart', $cart);
        $request->session()->put('cart_details', $cartDetails);
        $request->session()->put('cart_currencies', $cartCurrencies);
        if ($cart === []) {
            $request->session()->forget('cart_currency');
        } elseif ($cartCurrency !== null) {
            $request->session()->put('cart_currency', $cartCurrency);
        }

        return redirect()->route('cart.index')->with('success', __('store.cart_removed'));
    }

    public function destroySelected(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*' => ['accepted'],
        ]);

        $cart = $request->session()->get('cart', []);
        $cartDetails = $request->session()->get('cart_details', []);
        $cartCurrencies = $request->session()->get('cart_currencies', []);
        $selectedIds = array_map('strval', array_keys($validated['items']));

        foreach ($selectedIds as $variantId) {
            unset($cart[$variantId], $cartDetails[$variantId], $cartCurrencies[$variantId]);
        }

        $request->session()->put('cart', $cart);
        $request->session()->put('cart_details', $cartDetails);
        $request->session()->put('cart_currencies', $cartCurrencies);
        if ($cart === []) {
            $request->session()->forget('cart_currency');
        }

        return redirect()->route('cart.index')->with('success', __('store.cart_removed'));
    }

    private function validateQuantity(ProductVariant $variant, int $quantity): void
    {
        if (
            $variant->status !== 'active'
            || $variant->product->status !== 'active'
            || $quantity < $variant->minimum_quantity
        ) {
            throw ValidationException::withMessages([
                'quantity' => __('store.cart_quantity_invalid'),
            ]);
        }

        if ($quantity > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => __('store.stock_not_enough'),
            ]);
        }

        if ($quantity > $variant->maximum_quantity) {
            throw ValidationException::withMessages([
                'quantity' => __('store.cart_quantity_invalid'),
            ]);
        }

    }

    /**
     * @return array<string, string>
     */
    private function decryptDetails(?string $encryptedDetails): array
    {
        if ($encryptedDetails === null) {
            return [];
        }

        return json_decode(Crypt::decryptString($encryptedDetails), true, 512, JSON_THROW_ON_ERROR);
    }

    private function currency(string $currency): string
    {
        return in_array($currency, ['idr', 'myr', 'usd'], true) ? $currency : 'idr';
    }

    /**
     * @return array{float, string}
     */
    private function convertPrice(float $idrPrice, string $currency, ProductCurrencyRate $rates): array
    {
        return [$rates->convertIdrTo($idrPrice, $currency), $this->currencySymbol($currency)];
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

    /**
     * @param  array<int, array{key: string, label: string, type: string}>  $purchaseFields
     * @param  array<string, string>  $existingPurchaseDetails
     * @return array<string, array<int, string>>
     */
    private function purchaseFieldRules(array $purchaseFields, array $existingPurchaseDetails = []): array
    {
        if ($purchaseFields === []) {
            return ['purchase_details' => ['prohibited']];
        }

        $keys = array_column($purchaseFields, 'key');
        $rules = [
            'purchase_details' => ['required', 'array:'.implode(',', $keys)],
        ];

        foreach ($purchaseFields as $field) {
            $fieldRules = [
                $field['type'] === 'password' && filled($existingPurchaseDetails[$field['key']] ?? null)
                    ? 'nullable'
                    : 'required',
                'string',
                'max:255',
            ];

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
            }

            $rules['purchase_details.'.$field['key']] = $fieldRules;
        }

        return $rules;
    }
}
