<?php

use App\Models\Coupon;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lets administrators create, edit, list, and deactivate coupons', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.coupons.index'))
        ->assertSee('Coupons');
    $this->post(route('admin.coupons.store'), [
        'code' => 'welcome10',
        'discount_type' => 'percent',
        'discount_value' => '10',
        'stock' => '5',
        'is_active' => '1',
    ])->assertRedirect(route('admin.coupons.index'));

    $coupon = Coupon::query()->sole();
    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'code' => 'WELCOME10',
        'discount_type' => 'percent',
        'stock' => 5,
        'is_active' => true,
    ]);

    $this->put(route('admin.coupons.update', $coupon), [
        'code' => 'SAVE500',
        'discount_type' => 'fixed',
        'discount_value' => '500',
        'stock' => '10',
        'is_active' => '1',
    ])->assertRedirect(route('admin.coupons.index'));
    $this->patch(route('admin.coupons.status', $coupon), [])
        ->assertRedirect(route('admin.coupons.index'));

    $this->get(route('admin.coupons.index'))
        ->assertSee('SAVE500')
        ->assertSee('Inactive');
    $this->assertDatabaseHas('coupons', [
        'id' => $coupon->id,
        'code' => 'SAVE500',
        'discount_type' => 'fixed',
        'discount_value' => '500.00',
        'stock' => 10,
        'is_active' => false,
    ]);
});

it('rejects duplicate coupon codes regardless of letter case and blocks non-admins', function () {
    $coupon = Coupon::factory()->create(['code' => 'EXISTING']);
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create(['is_admin' => false]);

    $this->actingAs($customer)
        ->get(route('admin.coupons.index'))
        ->assertForbidden();
    $this->actingAs($admin)->post(route('admin.coupons.store'), [
        'code' => 'existing',
        'discount_type' => 'percent',
        'discount_value' => '10',
        'stock' => '5',
        'is_active' => '1',
    ])->assertSessionHasErrors('code');

    expect(Coupon::query()->count())->toBe(1)
        ->and($coupon->fresh()->code)->toBe('EXISTING');
});
