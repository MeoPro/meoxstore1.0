<?php

namespace App\Http\Controllers;

use App\Actions\AuthorizeOrderAccess;
use App\Models\Order;
use App\Models\OrderMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderMessageController extends Controller
{
    public function __construct(private AuthorizeOrderAccess $authorizeOrderAccess) {}

    public function poll(Request $request, Order $order): JsonResponse
    {
        $isAdminRoute = $request->routeIs('admin.orders.messages.*');
        $this->authorizeParticipant($request, $order, $isAdminRoute);

        return response()->json([
            'html' => $this->renderMessages($order, $isAdminRoute),
        ]);
    }

    public function store(Request $request, Order $order): JsonResponse|RedirectResponse
    {
        $isAdminRoute = $request->routeIs('admin.orders.messages.*');
        $this->authorizeParticipant($request, $order, $isAdminRoute);
        $this->ensureChatIsOpen($order);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $order, $isAdminRoute, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $this->authorizeParticipant($request, $lockedOrder, $isAdminRoute);
            $this->ensureChatIsOpen($lockedOrder);

            $lockedOrder->messages()->create([
                'user_id' => $request->user()?->id,
                'sender_type' => $isAdminRoute ? 'admin' : 'buyer',
                'body' => $validated['body'],
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('store.order_chat_message_sent'),
                'html' => $this->renderMessages($order, $isAdminRoute),
            ], 201);
        }

        return $this->redirectToOrder($order, $isAdminRoute)
            ->with('success', __('store.order_chat_message_sent'));
    }

    public function update(Request $request, Order $order, OrderMessage $message): RedirectResponse
    {
        $isAdminRoute = $request->routeIs('admin.orders.messages.*');
        $this->authorizeMessageWrite($request, $order, $message, $isAdminRoute, false);
        $this->ensureChatIsOpen($order);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($request, $order, $message, $isAdminRoute, $validated): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $lockedMessage = $lockedOrder->messages()->whereKey($message->id)->firstOrFail();
            $this->authorizeMessageWrite($request, $lockedOrder, $lockedMessage, $isAdminRoute, false);
            $this->ensureChatIsOpen($lockedOrder);

            $lockedMessage->update(['body' => $validated['body']]);
        });

        return $this->redirectToOrder($order, $isAdminRoute)
            ->with('success', __('store.order_chat_message_updated'));
    }

    public function destroy(Request $request, Order $order, OrderMessage $message): RedirectResponse
    {
        $isAdminRoute = $request->routeIs('admin.orders.messages.*');
        $this->authorizeMessageWrite($request, $order, $message, $isAdminRoute, true);
        $this->ensureChatIsOpen($order);

        DB::transaction(function () use ($request, $order, $message, $isAdminRoute): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $lockedMessage = $lockedOrder->messages()->whereKey($message->id)->firstOrFail();
            $this->authorizeMessageWrite($request, $lockedOrder, $lockedMessage, $isAdminRoute, true);
            $this->ensureChatIsOpen($lockedOrder);

            $lockedMessage->delete();
        });

        return $this->redirectToOrder($order, $isAdminRoute)
            ->with('success', __('store.order_chat_message_deleted'));
    }

    private function authorizeParticipant(Request $request, Order $order, bool $isAdminRoute): void
    {
        if ($isAdminRoute) {
            abort_unless((bool) $request->user()?->is_admin, 404);

            return;
        }

        $this->authorizeOrderAccess->handle($request, $order);
    }

    private function authorizeMessageWrite(
        Request $request,
        Order $order,
        OrderMessage $message,
        bool $isAdminRoute,
        bool $allowAdminModeration,
    ): void {
        $this->authorizeParticipant($request, $order, $isAdminRoute);

        if ($isAdminRoute && $allowAdminModeration) {
            return;
        }

        $expectedSender = $isAdminRoute ? 'admin' : 'buyer';
        abort_unless(
            $message->sender_type === $expectedSender
                && $message->user_id === $request->user()?->id,
            404,
        );
    }

    private function ensureChatIsOpen(Order $order): void
    {
        abort_unless(
            in_array($order->status, ['payment_submitted', 'payment_confirmed'], true),
            422,
            __('store.order_chat_closed'),
        );
    }

    private function renderMessages(Order $order, bool $isAdminRoute): string
    {
        return view('components.order-chat-messages', [
            'order' => $order,
            'messages' => $order->messages()->get(),
            'isAdmin' => $isAdminRoute,
            'canSendMessages' => in_array($order->status, ['payment_submitted', 'payment_confirmed'], true),
            'messageRoutePrefix' => $isAdminRoute ? 'admin.orders.messages' : 'orders.messages',
        ])->render();
    }

    private function redirectToOrder(Order $order, bool $isAdminRoute): RedirectResponse
    {
        return redirect()->route($isAdminRoute ? 'admin.orders.show' : 'orders.show', $order);
    }
}
