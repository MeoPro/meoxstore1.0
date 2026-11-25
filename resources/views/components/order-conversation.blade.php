@php
    $messageRoutePrefix = $isAdmin ? 'admin.orders.messages' : 'orders.messages';
@endphp

<section class="{{ $isAdmin ? 'border-top mt-4 pt-4' : 'order-card' }}" aria-labelledby="order-chat-heading">
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
        <div class="d-grid gap-3 mb-4">
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
                <article class="border rounded-3 p-3 bg-light">
                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                        <strong>{{ $senderLabel }}</strong>
                        <time class="text-muted small" datetime="{{ $message->created_at->toIso8601String() }}">
                            {{ $message->created_at->format('d M Y, H:i') }}
                        </time>
                    </div>
                    <p class="mb-0 text-break" style="white-space: pre-wrap">{{ $message->body }}</p>

                    @if ($canSendMessages && $isOwnMessage)
                        <details class="mt-3">
                            <summary class="text-primary">{{ __('store.order_chat_edit') }}</summary>
                            <form method="POST" action="{{ route($messageRoutePrefix.'.update', [$order, $message]) }}" class="mt-2">
                                @csrf
                                @method('PATCH')
                                <label class="form-label small" for="order-chat-edit-{{ $message->id }}">{{ __('store.order_chat_message_label') }}</label>
                                <textarea class="form-control mb-2" id="order-chat-edit-{{ $message->id }}" name="body" rows="3" maxlength="2000" required>{{ $message->body }}</textarea>
                                <button class="btn btn-sm btn-primary" type="submit">{{ __('store.order_chat_save') }}</button>
                            </form>
                        </details>
                    @endif

                    @if ($canSendMessages && ($isAdmin || $isOwnMessage))
                        <form method="POST" action="{{ route($messageRoutePrefix.'.destroy', [$order, $message]) }}" class="mt-2">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('store.order_chat_delete') }}</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    @endif

    @if ($canSendMessages)
        <form method="POST" action="{{ route($messageRoutePrefix.'.store', $order) }}">
            @csrf
            <label class="form-label" for="order-chat-message">
                {{ $isAdmin ? __('store.order_chat_reply') : __('store.order_chat_message_label') }}
            </label>
            <textarea class="form-control mb-2" id="order-chat-message" name="body" rows="4" maxlength="2000" required>{{ old('body') }}</textarea>
            <button class="btn btn-primary order-action" type="submit">{{ __('store.order_chat_send') }}</button>
        </form>
    @endif
</section>
