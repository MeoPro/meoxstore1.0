<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders the sign in page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Masuk')
        ->assertSee('name="email"', false)
        ->assertSee(route('register'));
});

it('registers a customer account for checkout and reviews', function () {
    $this->post(route('register.store'), [
        'name' => 'New Customer',
        'email' => 'NEW-CUSTOMER@example.test',
        'password' => 'secure-password',
        'password_confirmation' => 'secure-password',
    ])->assertRedirectToRoute('home');

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'name' => 'New Customer',
        'email' => 'new-customer@example.test',
        'is_admin' => false,
    ]);

    $this->get(route('admin.products.index'))->assertForbidden();
});

it('authenticates a user and redirects to the home page', function () {
    $user = User::factory()->create(['email' => 'member@example.test']);

    $this->post(route('login.store'), [
        'email' => 'MEMBER@EXAMPLE.TEST',
        'password' => 'password',
        'remember' => '1',
    ])->assertRedirectToRoute('home');

    $this->assertAuthenticatedAs($user);
});

it('keeps the current account when an authenticated user submits the login form', function () {
    $currentUser = User::factory()->create(['email' => 'current@example.test']);
    User::factory()->create(['email' => 'other@example.test']);
    $this->actingAs($currentUser);

    $this->post(route('login.store'), [
        'email' => 'other@example.test',
        'password' => 'password',
    ])->assertRedirectToRoute('home');

    $this->assertAuthenticatedAs($currentUser);
});

it('rejects invalid credentials without authenticating the user', function () {
    User::factory()->create(['email' => 'member@example.test']);

    $this->post(route('login.store'), [
        'email' => 'member@example.test',
        'password' => 'incorrect-password',
    ])->assertInvalid(['email']);

    $this->assertGuest();
});

it('rate limits repeated failed login attempts', function () {
    foreach (range(1, 5) as $attempt) {
        $this->post(route('login.store'), [
            'email' => 'missing@example.test',
            'password' => 'incorrect-password',
        ])->assertSessionHasErrors('email');
    }

    $this->post(route('login.store'), [
        'email' => 'missing@example.test',
        'password' => 'incorrect-password',
    ])->assertTooManyRequests();
});

it('logs out and invalidates the authenticated session', function () {
    $this->actingAs(User::factory()->create());

    $this->post(route('logout'))->assertRedirectToRoute('home');

    $this->assertGuest();
});

it('requires authentication to view a profile', function () {
    $this->get(route('profile.edit'))->assertRedirectToRoute('login');
    $this->get(route('profile.settings'))->assertRedirectToRoute('login');
});

it('shows sign in navigation to guests and moves logout into the authenticated profile', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('profile.edit'))
        ->assertDontSee('Keluar')
        ->assertDontSee('Masuk');

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('action="'.route('logout').'"', false)
        ->assertSee(__('store.log_out'));
});

it('renders and updates the authenticated profile', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old@example.test',
        'phone' => '0811111111',
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertOk()
        ->assertSee('Old Name')
        ->assertSee('old@example.test')
        ->assertSee(route('profile.settings'))
        ->assertSee(__('store.purchase_history'))
        ->assertDontSee('<h3>'.__('store.profile_title').'</h3>', false)
        ->assertDontSee('name="current_password"', false);
    $this->get(route('profile.settings'))
        ->assertOk()
        ->assertSee('name="phone" type="tel" value="0811111111"', false)
        ->assertSee('name="current_password"', false);

    $this->put(route('profile.update'), [
        'name' => 'New Name',
        'email' => 'NEW@example.test',
        'phone' => '08123456789',
    ])->assertRedirect();

    expect($user->fresh()->name)->toBe('New Name');
    expect($user->fresh()->email)->toBe('new@example.test');
    expect($user->fresh()->phone)->toBe('08123456789');
    expect($user->fresh()->email_verified_at)->toBeNull();
});

it('filters purchase history by the selected status and shows all by default', function () {
    $user = User::factory()->create();
    $unpaidOrder = Order::query()->create([
        'user_id' => $user->id,
        'status' => 'pending',
        'total' => '10000.00',
    ]);
    $verifyingOrder = Order::query()->create([
        'user_id' => $user->id,
        'status' => 'payment_submitted',
        'total' => '20000.00',
    ]);

    $this->actingAs($user)
        ->get(route('profile.edit'))
        ->assertSee($unpaidOrder->publicNumber())
        ->assertSee($verifyingOrder->publicNumber())
        ->assertSee('<i class="fa fa-receipt" data-status-icon="payment_submitted" aria-hidden="true"></i>', false)
        ->assertSee(route('profile.edit', ['status' => 'payment_submitted']).'#purchase-history', false);

    $this->get(route('profile.edit', ['status' => 'payment_submitted']))
        ->assertSee(__('store.order').' '.$verifyingOrder->publicNumber())
        ->assertDontSee(__('store.order').' '.$unpaidOrder->publicNumber())
        ->assertSee(route('profile.edit').'#purchase-history', false);
});

it('rejects an email address already used by another account', function () {
    $user = User::factory()->create(['email' => 'member@example.test']);
    User::factory()->create(['email' => 'taken@example.test']);

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'email' => 'taken@example.test',
        ])
        ->assertInvalid(['email']);

    expect($user->fresh()->email)->toBe('member@example.test');
});

it('changes the password only after verifying the current password', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->put(route('profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'current_password' => 'password',
        'password' => 'new-password-123',
        'password_confirmation' => 'new-password-123',
    ])->assertRedirect();

    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue();
});

it('rejects a password change when the current password is incorrect', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'current_password' => 'wrong-password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])
        ->assertInvalid(['current_password']);

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});
