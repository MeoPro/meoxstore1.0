@php
    $messageRoutePrefix = $isAdmin ? 'admin.orders.messages' : 'orders.messages';
@endphp

<section class="{{ $isAdmin ? 'border-top mt-4 pt-4' : 'order-card' }} order-chat" aria-labelledby="order-chat-heading">
    <h2 id="order-chat-heading">{{ __('store.order_chat_title') }}</h2>

    @if ($isAdmin && session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($isAdmin && $errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <p class="order-note">
        @if ($canSendMessages)
            {{ __('store.order_chat_active') }}
        @elseif ($order->status === 'pending')
            {{ __('store.order_chat_waiting') }}
        @else
            {{ __('store.order_chat_closed') }}
        @endif
    </p>

    @if ($order->messages->isEmpty())
        <p class="order-note">{{ __('store.order_chat_empty') }}</p>
    @else
        <div class="order-chat-list">
            @foreach ($order->messages as $message)
                @php
                    $isOwnMessage = $message->sender_type === ($isAdmin ? 'admin' : 'buyer')
                        && $message->user_id === auth()->id();
                    $senderLabel = $isOwnMessage
                        ? __('store.order_chat_you')
                        : ($message->sender_type === 'admin'
                            ? __('store.order_chat_admin')
                            : __('store.order_chat_buyer'));
                @endphp
                <article class="order-chat-row{{ $isOwnMessage ? ' is-own' : '' }}">
                    <div class="order-chat-bubble">
                        <div class="order-chat-bubble-header">
                            <strong class="order-chat-sender">{{ $senderLabel }}</strong>

                            @if ($canSendMessages && ($isAdmin || $isOwnMessage))
                                <details class="order-chat-menu">
                                    <summary class="order-chat-menu-toggle" aria-label="{{ __('store.order_chat_options') }}" title="{{ __('store.order_chat_options') }}">
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <circle cx="5" cy="12" r="1.7" />
                                            <circle cx="12" cy="12" r="1.7" />
                                            <circle cx="19" cy="12" r="1.7" />
                                        </svg>
                                    </summary>
                                    <div class="order-chat-menu-panel">
                                        @if ($isOwnMessage)
                                            <details class="order-chat-edit-menu">
                                                <summary class="order-chat-menu-item">{{ __('store.order_chat_edit') }}</summary>
                                                <form method="POST" action="{{ route($messageRoutePrefix.'.update', [$order, $message]) }}" class="order-chat-edit-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="form-label small" for="order-chat-edit-{{ $message->id }}">{{ __('store.order_chat_message_label') }}</label>
                                                    <textarea class="form-control mb-2" id="order-chat-edit-{{ $message->id }}" name="body" rows="3" maxlength="2000" required>{{ $message->body }}</textarea>
                                                    <button class="btn btn-sm btn-primary" type="submit">{{ __('store.order_chat_save') }}</button>
                                                </form>
                                            </details>
                                        @endif

                                        <form method="POST" action="{{ route($messageRoutePrefix.'.destroy', [$order, $message]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="order-chat-menu-item is-danger" type="submit">{{ __('store.order_chat_delete') }}</button>
                                        </form>
                                    </div>
                                </details>
                            @endif
                        </div>

                        <p class="order-chat-message">{{ $message->body }}</p>

                        <div class="order-chat-message-footer">
                            <time datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->format('d M Y, H:i') }}">
                                {{ $message->created_at->format('H:i') }}
                            </time>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    @if ($canSendMessages)
        <form method="POST" action="{{ route($messageRoutePrefix.'.store', $order) }}" class="order-chat-compose" data-order-chat-compose data-sending-label="{{ __('store.order_chat_sending') }}">
            @csrf
            <label class="visually-hidden" for="order-chat-message">
                {{ $isAdmin ? __('store.order_chat_reply') : __('store.order_chat_message_label') }}
            </label>
            <textarea class="form-control" id="order-chat-message" name="body" rows="2" maxlength="2000" placeholder="{{ $isAdmin ? __('store.order_chat_reply') : __('store.order_chat_message_label') }}" required>{{ old('body') }}</textarea>
            <button class="btn btn-primary order-action" type="submit" data-order-chat-send>{{ __('store.order_chat_send') }}</button>
        </form>
    @endif
</section>

<script>
    document.querySelectorAll('[data-order-chat-compose]').forEach((form) => {
        let isSubmitting = false;

        form.addEventListener('submit', (event) => {
            if (isSubmitting) {
                event.preventDefault();
                return;
            }

            isSubmitting = true;
            const button = form.querySelector('[data-order-chat-send]');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.textContent = form.dataset.sendingLabel;
        });
    });
</script>

<style>
    .order-chat { color: #233247; }
    .order-chat-list { display: flex; flex-direction: column; gap: 12px; margin: 18px 0; }
    .order-chat-row { display: flex; justify-content: flex-start; }
    .order-chat-row.is-own { justify-content: flex-end; }
    .order-chat-bubble {
        background: #fff;
        border: 1px solid #e4eaf2;
        border-radius: 16px 16px 16px 5px;
        box-shadow: 0 2px 6px rgba(27, 46, 75, .045);
        max-width: min(84%, 640px);
        min-width: 120px;
        padding: 10px 12px 7px;
        position: relative;
    }
    .order-chat-row.is-own .order-chat-bubble {
        background: #e8f3ff;
        border-color: #d7e9fb;
        border-radius: 16px 16px 5px 16px;
    }
    .order-chat-bubble-header { align-items: center; display: flex; gap: 12px; justify-content: space-between; min-height: 24px; }
    .order-chat-sender { color: #2877c9; font-size: 11px; font-weight: 700; }
    .order-chat-row.is-own .order-chat-sender { color: #2265a8; }
    .order-chat-message { color: #24354b; font-size: 13px; line-height: 1.55; margin: 5px 0 2px; overflow-wrap: anywhere; white-space: pre-wrap; }
    .order-chat-message-footer { color: #8190a3; display: flex; font-size: 10px; justify-content: flex-end; line-height: 1; padding-top: 3px; }
    .order-chat-menu { flex: 0 0 auto; position: relative; }
    .order-chat-menu-toggle {
        align-items: center;
        border-radius: 50%;
        color: #73839a;
        cursor: pointer;
        display: flex;
        height: 28px;
        justify-content: center;
        list-style: none;
        width: 28px;
    }
    .order-chat-menu-toggle::-webkit-details-marker,
    .order-chat-menu-item::-webkit-details-marker { display: none; }
    .order-chat-menu-toggle:hover,
    .order-chat-menu[open] > .order-chat-menu-toggle { background: rgba(67, 104, 151, .1); color: #256eb5; }
    .order-chat-menu-toggle svg { fill: currentColor; height: 18px; width: 18px; }
    .order-chat-menu-panel {
        background: #fff;
        border: 1px solid #e3e9f1;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(24, 42, 68, .14);
        min-width: 150px;
        padding: 5px;
        position: absolute;
        right: 0;
        top: calc(100% + 4px);
        z-index: 5;
    }
    .order-chat-menu-panel form { margin: 0; }
    .order-chat-menu-item {
        background: transparent;
        border: 0;
        border-radius: 7px;
        color: #34445a;
        cursor: pointer;
        display: block;
        font: inherit;
        font-size: 12px;
        list-style: none;
        padding: 8px 10px;
        text-align: left;
        text-decoration: none;
        width: 100%;
    }
    .order-chat-menu-item:hover { background: #f3f6fa; color: #1e5c9b; }
    .order-chat-menu-item.is-danger { color: #b42318; }
    .order-chat-menu-item.is-danger:hover { background: #fff1f0; color: #912018; }
    .order-chat-edit-form { background: #fff; padding: 8px; width: min(260px, calc(100vw - 48px)); }
    .order-chat-edit-form .form-control { font-size: 12px; min-height: 0; }
    .order-chat-compose { align-items: flex-end; display: flex; gap: 10px; margin-top: 16px; }
    .order-chat-compose .form-control { border-color: #dfe5ec; border-radius: 12px; min-height: 44px; resize: vertical; }
    .order-chat-compose .order-action { flex: 0 0 auto; min-height: 44px; }
    .order-chat-compose .order-action:disabled { cursor: wait; opacity: .7; }
    @media (max-width: 575.98px) {
        .order-chat-bubble { max-width: 92%; }
        .order-chat-compose { align-items: stretch; flex-direction: column; }
        .order-chat-compose .order-action { align-self: flex-end; }
    }
</style>
