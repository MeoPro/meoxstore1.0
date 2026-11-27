<?php

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('records and deletes expenses with their quantity and unit price', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.expenses.index'))
        ->assertOk()
        ->assertSee('name="quantity"', false)
        ->assertSee('value="1"', false);

    $this->post(route('admin.expenses.store'), [
        'description' => 'Packaging materials',
        'quantity' => 3,
        'unit_price' => '2500.00',
    ])->assertRedirect(route('admin.expenses.index'))
        ->assertSessionHas('success');

    $expense = Expense::query()->sole();
    expect($expense->description)->toBe('Packaging materials')
        ->and($expense->quantity)->toBe(3)
        ->and((float) $expense->unit_price)->toBe(2500.0);

    $this->get(route('admin.expenses.index'))
        ->assertSee('Rp 7.500');

    $this->delete(route('admin.expenses.destroy', $expense))
        ->assertRedirect(route('admin.expenses.index'));
    $this->assertModelMissing($expense);
});

it('requires admin access and rejects invalid expense amounts', function () {
    $this->get(route('admin.expenses.index'))
        ->assertRedirectToRoute('login');

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.expenses.index'))
        ->assertForbidden();

    $admin = User::factory()->create(['is_admin' => true]);
    $this->actingAs($admin)
        ->post(route('admin.expenses.store'), [
            'description' => 'Invalid expense',
            'quantity' => 1,
            'unit_price' => 0,
        ])
        ->assertInvalid(['unit_price']);

    $this->assertDatabaseCount('expenses', 0);
});
