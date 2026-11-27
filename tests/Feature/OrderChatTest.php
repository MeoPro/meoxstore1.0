<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows buyers to manage their own messages after submitting payment proof', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_submitted',
        'total' => '10000.00',
    ]);

    $this->actingAs($buyer)
        ->post(route('orders.messages.store', $order), ['body' => 'I need help with my order.'])
        ->assertRedirect(route('orders.show', $order));

    $message = $order->messages()->firstOrFail();
    $this->assertDatabaseHas('order_messages', [
        'id' => $message->id,
        'user_id' => $buyer->id,
        'sender_type' => 'buyer',
        'body' => 'I need help with my order.',
    ]);

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertSee(__('store.order_chat_title'))
        ->assertSee(__('store.order_chat_active'))
        ->assertSee('I need help with my order.')
        ->assertSee('order-chat-row is-own', false)
        ->assertSee('class="order-chat-menu-toggle"', false)
        ->assertSee($message->created_at->format('H:i'))
        ->assertSee('data-order-chat-compose', false)
        ->assertSee('data-order-chat-send', false)
        ->assertSee('data-sending-label="'.__('store.order_chat_sending').'"', false)
        ->assertSee('data-order-chat-poller', false)
        ->assertSee('data-messages-url="'.route('orders.messages.poll', $order).'"', false)
        ->assertSee('name="body"', false);

    $this->actingAs($buyer)
        ->patch(route('orders.messages.update', [$order, $message]), ['body' => 'Updated question.'])
        ->assertRedirect(route('orders.show', $order));

    $this->assertDatabaseHas('order_messages', [
        'id' => $message->id,
        'body' => 'Updated question.',
    ]);

    $this->actingAs($buyer)
        ->delete(route('orders.messages.destroy', [$order, $message]))
        ->assertRedirect(route('orders.show', $order));

    $this->assertDatabaseMissing('order_messages', ['id' => $message->id]);
});

it('provides newly received messages to authorized order chat polling requests', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.orders.messages.store', $order), ['body' => 'Admin replied while buyer page stayed open.'])
        ->assertRedirect(route('admin.orders.show', $order));

    $response = $this->actingAs($buyer)
        ->getJson(route('orders.messages.poll', $order))
        ->assertOk();

    expect($response->json('html'))->toContain('Admin replied while buyer page stayed open.');

    $otherOrder = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '15000.00',
    ]);
    $otherOrder->messages()->create([
        'user_id' => $admin->id,
        'sender_type' => 'admin',
        'body' => 'This message belongs to a different order.',
    ]);

    $otherOrderResponse = $this->actingAs($buyer)
        ->getJson(route('orders.messages.poll', $order))
        ->assertOk();

    expect($otherOrderResponse->json('html'))->not->toContain('This message belongs to a different order.');
});

it('lets admins reply and moderate buyer messages but only edit their own', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $admin = User::factory()->create(['is_admin' => true]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_submitted',
        'total' => '10000.00',
    ]);
    $buyerMessage = $order->messages()->create([
        'user_id' => $buyer->id,
        'sender_type' => 'buyer',
        'body' => 'Original buyer question.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertSee(__('store.order_chat_title'))
        ->assertSee('Original buyer question.')
        ->assertSee(__('store.order_chat_reply'))
        ->assertSee(__('store.order_chat_options'))
        ->assertSee('class="order-chat-menu-toggle"', false);

    $this->actingAs($admin)
        ->post(route('admin.orders.messages.store', $order), ['body' => 'Admin reply.'])
        ->assertRedirect(route('admin.orders.show', $order));

    $adminMessage = $order->messages()->where('sender_type', 'admin')->firstOrFail();
    $this->assertDatabaseHas('order_messages', [
        'id' => $adminMessage->id,
        'user_id' => $admin->id,
        'sender_type' => 'admin',
        'body' => 'Admin reply.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.messages.update', [$order, $adminMessage]), ['body' => 'Updated admin reply.'])
        ->assertRedirect(route('admin.orders.show', $order));

    $this->assertDatabaseHas('order_messages', [
        'id' => $adminMessage->id,
        'body' => 'Updated admin reply.',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.orders.messages.update', [$order, $buyerMessage]), ['body' => 'Changed by admin.'])
        ->assertNotFound();

    $this->actingAs($buyer)
        ->patch(route('orders.messages.update', [$order, $adminMessage]), ['body' => 'Changed by buyer.'])
        ->assertNotFound();

    $this->actingAs($buyer)
        ->delete(route('orders.messages.destroy', [$order, $adminMessage]))
        ->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('admin.orders.messages.destroy', [$order, $buyerMessage]))
        ->assertRedirect(route('admin.orders.show', $order));

    $this->assertDatabaseMissing('order_messages', ['id' => $buyerMessage->id]);
});

it('lets verified guests manage messages only within their accessible order', function () {
    $guestOrder = Order::query()->create([
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);
    $otherOrder = Order::query()->create([
        'status' => 'payment_confirmed',
        'total' => '15000.00',
    ]);
    $otherMessage = $otherOrder->messages()->create([
        'sender_type' => 'buyer',
        'body' => 'Message from another order.',
    ]);

    $this->post(route('orders.messages.store', $guestOrder), ['body' => 'Guest message.'])
        ->assertNotFound();

    $this->withSession(['guest_orders' => [$guestOrder->id => true]])
        ->post(route('orders.messages.store', $guestOrder), ['body' => 'Guest message.'])
        ->assertRedirect(route('orders.show', $guestOrder));

    $guestMessage = $guestOrder->messages()->firstOrFail();
    $this->withSession(['guest_orders' => [$guestOrder->id => true]])
        ->patch(route('orders.messages.update', [$guestOrder, $guestMessage]), ['body' => 'Updated guest message.'])
        ->assertRedirect(route('orders.show', $guestOrder));

    $this->withSession(['guest_orders' => [$guestOrder->id => true]])
        ->patch(route('orders.messages.update', [$guestOrder, $otherMessage]), ['body' => 'Cross-order change.'])
        ->assertNotFound();

    $this->assertDatabaseHas('order_messages', [
        'id' => $guestMessage->id,
        'user_id' => null,
        'body' => 'Updated guest message.',
    ]);
});

it('rejects new messages until the buyer submits payment proof', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'pending',
        'total' => '10000.00',
    ]);

    $this->actingAs($buyer)
        ->post(route('orders.messages.store', $order), ['body' => 'Too early.'])
        ->assertUnprocessable();

    $this->assertDatabaseCount('order_messages', 0);
});

it('makes completed order chats read-only', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'completed',
        'total' => '10000.00',
    ]);
    $message = $order->messages()->create([
        'user_id' => $buyer->id,
        'sender_type' => 'buyer',
        'body' => 'Existing conversation.',
    ]);

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertSee('Existing conversation.')
        ->assertSee(__('store.order_chat_closed'))
        ->assertDontSee('name="body"', false);

    $this->actingAs($buyer)
        ->post(route('orders.messages.store', $order), ['body' => 'After completion.'])
        ->assertUnprocessable();

    $this->actingAs($buyer)
        ->patch(route('orders.messages.update', [$order, $message]), ['body' => 'Changed after completion.'])
        ->assertUnprocessable();

    $this->actingAs($buyer)
        ->delete(route('orders.messages.destroy', [$order, $message]))
        ->assertUnprocessable();

    $this->assertDatabaseHas('order_messages', [
        'id' => $message->id,
        'body' => 'Existing conversation.',
    ]);
});

it('rejects empty and overlong messages', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);

    $this->actingAs($buyer)
        ->from(route('orders.show', $order))
        ->post(route('orders.messages.store', $order), ['body' => ''])
        ->assertSessionHasErrors([
            'body' => __('validation.required', ['attribute' => 'body']),
        ]);

    $this->actingAs($buyer)
        ->from(route('orders.show', $order))
        ->post(route('orders.messages.store', $order), ['body' => str_repeat('x', 2001)])
        ->assertSessionHasErrors([
            'body' => __('validation.max.string', ['attribute' => 'body', 'max' => 2000]),
        ]);

    $this->assertDatabaseCount('order_messages', 0);
});

it('hides order conversations from other buyers and non-admins', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $otherBuyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);

    $this->actingAs($otherBuyer)
        ->get(route('orders.show', $order))
        ->assertNotFound();

    $this->actingAs($otherBuyer)
        ->post(route('orders.messages.store', $order), ['body' => 'Unauthorized.'])
        ->assertNotFound();

    $this->actingAs($otherBuyer)
        ->post(route('admin.orders.messages.store', $order), ['body' => 'Unauthorized admin message.'])
        ->assertForbidden();

    $this->assertDatabaseCount('order_messages', 0);
});

it('escapes buyer message content in the order conversation', function () {
    $buyer = User::factory()->create(['is_admin' => false]);
    $order = Order::query()->create([
        'user_id' => $buyer->id,
        'status' => 'payment_confirmed',
        'total' => '10000.00',
    ]);
    $body = '<script>alert("xss")</script>';
    $order->messages()->create([
        'user_id' => $buyer->id,
        'sender_type' => 'buyer',
        'body' => $body,
    ]);

    $this->actingAs($buyer)
        ->get(route('orders.show', $order))
        ->assertSee($body)
        ->assertDontSee($body, false);
});
