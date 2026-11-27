@php
    $messageVersion = hash('sha256', $messages->map(fn ($message) => implode(':', [
        $message->id,
        $message->sender_type,
        $message->user_id ?? 'guest',
        $message->updated_at?->getTimestamp(),
        hash('sha256', $message->body),
    ]))->implode('|'));
@endphp

<div class="order-chat-list" data-order-chat-list data-version="{{ $messageVersion }}">
    @forelse ($messages as $message)
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
    @empty
        <p class="order-note">{{ __('store.order_chat_empty') }}</p>
    @endforelse
</div>
