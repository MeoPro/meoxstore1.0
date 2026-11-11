@extends('admin.layout')

@section('title', 'Order #'.$order->id)

@section('content')
    <div class="page-heading">
        <div><h1>Order #{{ $order->id }}</h1></div>
        <a class="btn btn-light border" href="{{ route('admin.orders.index') }}">Back to orders</a>
    </div>
    <div class="panel p-4">
        <p><strong>Customer:</strong> {{ $order->user?->name ?? 'Guest customer' }} ({{ $order->contact_email ?? $order->user?->email }})</p>
        @if ($order->contact_phone)
            <p><strong>Phone:</strong> {{ $order->contact_phone }}</p>
        @endif
        <p><strong>Status:</strong> {{ __('store.status_'.$order->status) }}</p>
        @if ($order->payment_proof_path)
            <p><strong>Payment proof:</strong> <a href="{{ route('admin.orders.payment-proof', $order) }}">View uploaded proof</a></p>
        @endif
        @foreach ($order->items as $item)
            <div class="border-top py-3 d-flex justify-content-between gap-3">
                <span>{{ $item->product_name }} — {{ $item->variant_name }} × {{ $item->quantity }}</span>
                <strong>Rp {{ number_format((float) $item->unit_price * $item->quantity, 0, ',', '.') }}</strong>
            </div>
            @if ($item->customer_details)
                <div class="border-top py-3">
                    <strong>{{ __('store.purchase_details') }}</strong>
                    <dl class="row mb-0 mt-2">
                        @foreach ($item->customer_details as $field => $value)
                            @if ($field === 'purchase_details')
                                @foreach ($value as $purchaseFieldKey => $purchaseFieldValue)
                                    @php($purchaseField = collect($item->product?->purchaseFields() ?? [])->firstWhere('key', $purchaseFieldKey))
                                    <dt class="col-sm-4">{{ $purchaseField['label'] ?? $purchaseFieldKey }}</dt>
                                    <dd class="col-sm-8">{{ $purchaseFieldValue }}</dd>
                                @endforeach
                            @elseif ($field === 'seller_note')
                                <dt class="col-sm-4">{{ __('store.seller_note') }}</dt>
                                <dd class="col-sm-8">{{ $value }}</dd>
                            @else
                                <dt class="col-sm-4">{{ __('store.'.$field) }}</dt>
                                <dd class="col-sm-8">{{ $value }}</dd>
                            @endif
                        @endforeach
                    </dl>
                </div>
            @endif
        @endforeach
        @if (in_array($order->status, ['payment_confirmed', 'completed'], true))
            <form class="border-top pt-4 mt-3" method="POST" action="{{ route('admin.orders.delivery-details', $order) }}" data-delivery-details-form>
                @csrf
                @method('PATCH')
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-1">{{ __('store.delivery_details') }}</h2>
                        <p class="text-muted mb-0">{{ __('store.delivery_details_help') }}</p>
                    </div>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-add-delivery-detail>{{ __('store.add_delivery_detail') }}</button>
                </div>
                @php($deliveryDetails = old('delivery_details', $order->delivery_details ?? []))
                <div class="d-grid gap-3" id="delivery-details-list" data-next-index="{{ count($deliveryDetails) }}">
                    @foreach ($deliveryDetails as $index => $deliveryDetail)
                        <div class="row g-2 align-items-end" data-delivery-row>
                            <div class="col-md-4">
                                <label class="form-label" for="delivery-label-{{ $index }}">{{ __('store.delivery_detail_label') }}</label>
                                <input class="form-control" id="delivery-label-{{ $index }}" name="delivery_details[{{ $index }}][label]" value="{{ $deliveryDetail['label'] ?? '' }}" maxlength="100" required>
                            </div>
                            <div class="col">
                                <label class="form-label" for="delivery-value-{{ $index }}">{{ __('store.delivery_detail_value') }}</label>
                                <textarea class="form-control" id="delivery-value-{{ $index }}" name="delivery_details[{{ $index }}][value]" rows="2" maxlength="2000" required>{{ $deliveryDetail['value'] ?? '' }}</textarea>
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-outline-danger" type="button" data-remove-delivery-detail>{{ __('store.remove_delivery_detail') }}</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <template id="delivery-detail-template">
                    <div class="row g-2 align-items-end" data-delivery-row>
                        <div class="col-md-4">
                            <label class="form-label" for="delivery-label-__INDEX__">{{ __('store.delivery_detail_label') }}</label>
                            <input class="form-control" id="delivery-label-__INDEX__" data-name="delivery_details[__INDEX__][label]" maxlength="100" required>
                        </div>
                        <div class="col">
                            <label class="form-label" for="delivery-value-__INDEX__">{{ __('store.delivery_detail_value') }}</label>
                            <textarea class="form-control" id="delivery-value-__INDEX__" data-name="delivery_details[__INDEX__][value]" rows="2" maxlength="2000" required></textarea>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-outline-danger" type="button" data-remove-delivery-detail>{{ __('store.remove_delivery_detail') }}</button>
                        </div>
                    </div>
                </template>
                @error('delivery_details')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                <p class="small text-muted mt-3 mb-0" role="status" aria-live="polite"
                    data-delivery-save-state
                    data-saving-text="{{ __('store.delivery_details_saving') }}"
                    data-saved-text="{{ __('store.delivery_details_saved') }}"
                    data-unsaved-text="{{ __('store.delivery_details_unsaved') }}"
                    data-error-text="{{ __('store.delivery_details_save_error') }}">
                    {{ __('store.delivery_details_autosave') }}
                </p>
            </form>
            <script>
                (() => {
                    const deliveryForm = document.querySelector('[data-delivery-details-form]');
                    const list = document.getElementById('delivery-details-list');
                    const template = document.getElementById('delivery-detail-template');
                    const saveState = document.querySelector('[data-delivery-save-state]');
                    const statusForm = document.querySelector('form[action="{{ route('admin.orders.status', $order) }}"]');
                    let nextIndex = Number(list.dataset.nextIndex);
                    let saveTimer;
                    let saveQueue = Promise.resolve();
                    let hasUnsavedChanges = false;
                    let allowCompletionSubmit = false;

                    const rowsAreComplete = () => [...list.querySelectorAll('[data-delivery-row] input, [data-delivery-row] textarea')]
                        .every((field) => field.value.trim() !== '');

                    const saveDeliveryDetails = () => {
                        clearTimeout(saveTimer);

                        if (!hasUnsavedChanges) {
                            return saveQueue;
                        }

                        saveQueue = saveQueue.catch(() => {}).then(async () => {
                            if (!hasUnsavedChanges) {
                                return;
                            }

                            hasUnsavedChanges = false;
                            saveState.textContent = saveState.dataset.savingText;
                            const response = await fetch(deliveryForm.action, {
                                method: 'POST',
                                headers: {
                                    Accept: 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: new URLSearchParams(new FormData(deliveryForm)),
                                credentials: 'same-origin',
                            });

                            if (!response.ok) {
                                const result = await response.json().catch(() => ({}));
                                hasUnsavedChanges = true;
                                throw new Error(result.message || saveState.dataset.errorText);
                            }

                            saveState.textContent = saveState.dataset.savedText;
                        });

                        return saveQueue;
                    };

                    const scheduleSave = () => {
                        hasUnsavedChanges = true;
                        saveState.textContent = rowsAreComplete()
                            ? saveState.dataset.savingText
                            : saveState.dataset.unsavedText;
                        clearTimeout(saveTimer);

                        if (rowsAreComplete()) {
                            saveTimer = setTimeout(() => {
                                saveDeliveryDetails().catch(() => {
                                    saveState.textContent = saveState.dataset.errorText;
                                });
                            }, 500);
                        }
                    };

                    document.querySelector('[data-add-delivery-detail]').addEventListener('click', () => {
                        const row = template.content.cloneNode(true);
                        row.querySelectorAll('[data-name]').forEach((input) => {
                            input.name = input.dataset.name.replace('__INDEX__', String(nextIndex));
                            input.id = input.id.replace('__INDEX__', String(nextIndex));
                            input.previousElementSibling.htmlFor = input.id;
                        });
                        nextIndex += 1;
                        list.append(row);
                        scheduleSave();
                    });

                    list.addEventListener('input', scheduleSave);
                    list.addEventListener('click', (event) => {
                        if (event.target.closest('[data-remove-delivery-detail]')) {
                            event.target.closest('[data-delivery-row]').remove();
                            scheduleSave();
                        }
                    });

                    statusForm?.addEventListener('submit', async (event) => {
                        if (allowCompletionSubmit) {
                            allowCompletionSubmit = false;
                            return;
                        }

                        if (event.submitter?.value !== 'completed') {
                            return;
                        }

                        event.preventDefault();

                        if (!deliveryForm.reportValidity()) {
                            return;
                        }

                        try {
                            while (hasUnsavedChanges) {
                                await saveDeliveryDetails();
                            }

                            allowCompletionSubmit = true;
                            statusForm.requestSubmit(event.submitter);
                        } catch (error) {
                            saveState.textContent = error.message || saveState.dataset.errorText;
                        }
                    });
                })();
            </script>
        @endif
        <div class="border-top pt-3 d-flex justify-content-between">
            <strong>Total</strong>
            <strong>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</strong>
        </div>
        @if (in_array($order->status, ['pending', 'payment_submitted', 'payment_confirmed'], true))
            <form class="d-flex flex-wrap gap-2 mt-4" method="POST" action="{{ route('admin.orders.status', $order) }}">
                @csrf
                @method('PATCH')
                @if ($order->status === 'payment_submitted')
                    <button class="btn btn-accent" type="submit" name="status" value="payment_confirmed">{{ __('store.accept_order') }}</button>
                @endif
                @if ($order->status === 'payment_confirmed')
                    <button class="btn btn-success" type="submit" name="status" value="completed">Mark completed</button>
                @endif
                <button class="btn btn-outline-danger" type="submit" name="status" value="cancelled" onclick="return confirm('Cancel order and return its stock?')">Cancel order</button>
            </form>
        @endif
    </div>
@endsection
