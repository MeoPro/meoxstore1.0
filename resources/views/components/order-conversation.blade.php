@php
    $messageRoutePrefix = $isAdmin ? 'admin.orders.messages' : 'orders.messages';
    $messages = $order->messages;
@endphp

<section
    class="{{ $isAdmin ? 'border-top mt-4 pt-4' : 'order-card' }} order-chat"
    aria-labelledby="order-chat-heading"
    data-order-chat-poller
    data-messages-url="{{ route($messageRoutePrefix.'.poll', $order) }}"
    data-poll-error="{{ __('store.order_chat_update_failed') }}"
>
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

    <div data-order-chat-message-container>
        @include('components.order-chat-messages', [
            'order' => $order,
            'messages' => $messages,
            'isAdmin' => $isAdmin,
            'canSendMessages' => $canSendMessages,
            'messageRoutePrefix' => $messageRoutePrefix,
        ])
    </div>
    <p class="order-note" data-order-chat-poll-status aria-live="polite" hidden></p>

    @if ($canSendMessages)
        <form method="POST" action="{{ route($messageRoutePrefix.'.store', $order) }}" class="order-chat-compose" data-order-chat-compose data-sending-label="{{ __('store.order_chat_sending') }}" data-send-error="{{ __('store.order_chat_send_failed') }}">
            @csrf
            <label class="visually-hidden" for="order-chat-message">
                {{ $isAdmin ? __('store.order_chat_reply') : __('store.order_chat_message_label') }}
            </label>
            <textarea class="form-control" id="order-chat-message" name="body" rows="2" maxlength="2000" placeholder="{{ $isAdmin ? __('store.order_chat_reply') : __('store.order_chat_message_label') }}" required>{{ old('body') }}</textarea>
            <button class="btn btn-primary order-action" type="submit" data-order-chat-send>{{ __('store.order_chat_send') }}</button>
        </form>
        <p class="order-note order-chat-compose-status" data-order-chat-compose-status aria-live="polite" hidden></p>
    @endif
</section>

<script>
    document.querySelectorAll('[data-order-chat-poller]').forEach((chat) => {
        const messageContainer = chat.querySelector('[data-order-chat-message-container]');
        const pollStatus = chat.querySelector('[data-order-chat-poll-status]');
        const composeForm = chat.querySelector('[data-order-chat-compose]');
        let failureReported = false;
        let isSubmitting = false;
        let messageRefreshGeneration = 0;

        const replaceMessageList = (html, forceScroll = false) => {
            const template = document.createElement('template');
            template.innerHTML = html;
            const incomingList = template.content.querySelector('[data-order-chat-list]');
            const currentList = messageContainer.querySelector('[data-order-chat-list]');
            if (!incomingList || !currentList) {
                throw new TypeError('Chat response did not include a message list.');
            }

            if (incomingList.dataset.version !== currentList.dataset.version) {
                const previousScrollTop = currentList.scrollTop;
                const wasNearBottom = currentList.scrollHeight - currentList.scrollTop - currentList.clientHeight <= 80;
                currentList.replaceWith(incomingList);
                if (forceScroll || wasNearBottom) {
                    incomingList.scrollTop = incomingList.scrollHeight;
                } else {
                    incomingList.scrollTop = previousScrollTop;
                }
            }
        };

        const pollMessages = async () => {
            if (document.visibilityState === 'hidden') {
                window.setTimeout(pollMessages, 5000);
                return;
            }

            const refreshGeneration = messageRefreshGeneration;

            try {
                const response = await fetch(chat.dataset.messagesUrl, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    cache: 'no-store',
                    credentials: 'same-origin',
                });
                if (!response.ok) {
                    throw new Error(`Chat refresh returned HTTP ${response.status}.`);
                }

                const payload = await response.json();
                if (typeof payload.html !== 'string') {
                    throw new TypeError('Chat refresh returned an invalid response.');
                }

                if (refreshGeneration === messageRefreshGeneration && !isSubmitting) {
                    replaceMessageList(payload.html);
                    pollStatus.hidden = true;
                    pollStatus.textContent = '';
                    failureReported = false;
                }
            } catch (error) {
                if (refreshGeneration === messageRefreshGeneration && !isSubmitting) {
                    pollStatus.textContent = chat.dataset.pollError;
                    pollStatus.hidden = false;
                    if (!failureReported) {
                        console.error('Unable to refresh order chat messages.', error);
                        failureReported = true;
                    }
                }
            }

            window.setTimeout(pollMessages, 3000);
        };

        if (composeForm) {
            const sendButton = composeForm.querySelector('[data-order-chat-send]');
            const messageInput = composeForm.querySelector('textarea');
            const composeStatus = chat.querySelector('[data-order-chat-compose-status]');
            const sendLabel = sendButton.textContent;

            composeForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (isSubmitting) {
                    return;
                }

                isSubmitting = true;
                messageRefreshGeneration += 1;
                sendButton.disabled = true;
                sendButton.setAttribute('aria-busy', 'true');
                sendButton.textContent = composeForm.dataset.sendingLabel;
                composeStatus.hidden = false;
                composeStatus.classList.remove('is-error');
                composeStatus.textContent = composeForm.dataset.sendingLabel;

                try {
                    const response = await fetch(composeForm.action, {
                        method: 'POST',
                        body: new FormData(composeForm),
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });
                    const payload = await response.json();

                    if (!response.ok) {
                        const validationMessage = response.status === 422
                            ? (payload.errors && payload.errors.body && payload.errors.body[0]) || payload.message
                            : null;
                        composeStatus.textContent = validationMessage || composeForm.dataset.sendError;
                        composeStatus.classList.add('is-error');
                        if (!validationMessage) {
                            console.error('Order chat send failed with HTTP status.', response.status);
                        }
                        return;
                    }

                    if (payload.success !== true || typeof payload.message !== 'string' || typeof payload.html !== 'string') {
                        throw new TypeError('Chat send returned an invalid response.');
                    }

                    messageInput.value = '';
                    composeStatus.textContent = payload.message;
                    pollStatus.hidden = true;
                    pollStatus.textContent = '';
                    messageRefreshGeneration += 1;
                    replaceMessageList(payload.html, true);
                } catch (error) {
                    composeStatus.textContent = composeForm.dataset.sendError;
                    composeStatus.classList.add('is-error');
                    console.error('Unable to send order chat message.', error);
                } finally {
                    isSubmitting = false;
                    sendButton.disabled = false;
                    sendButton.removeAttribute('aria-busy');
                    sendButton.textContent = sendLabel;
                }
            });
        }

        const messageList = messageContainer.querySelector('[data-order-chat-list]');
        window.requestAnimationFrame(() => {
            messageList.scrollTop = messageList.scrollHeight;
        });
        window.setTimeout(pollMessages, 3000);
    });
</script>

<style>
    .order-chat { color: #233247; }
    .order-chat-list { display: flex; flex-direction: column; gap: 12px; height: 360px; margin: 18px 0; overflow-y: auto; overscroll-behavior-y: contain; padding: 10px; scrollbar-gutter: stable; }
    .order-chat-list::-webkit-scrollbar { width: 7px; }
    .order-chat-list::-webkit-scrollbar-thumb { background: #c6d1df; border-radius: 999px; }
    .order-chat-list::-webkit-scrollbar-track { background: #f3f6fa; border-radius: 999px; }
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
    .order-chat-compose-status { margin-top: 8px; }
    .order-chat-compose-status.is-error { color: #b42318; }
    @media (max-width: 575.98px) {
        .order-chat-list { height: 320px; margin: 14px 0; padding: 8px; }
        .order-chat-bubble { max-width: 92%; }
        .order-chat-compose { align-items: stretch; flex-direction: column; }
        .order-chat-compose .order-action { align-self: flex-end; }
    }
</style>
