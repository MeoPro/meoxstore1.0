<?php

use App\Models\SocialLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('restricts social media management to administrators', function () {
    $this->get(route('admin.social-links.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['is_admin' => false]))
        ->get(route('admin.social-links.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['is_admin' => true]))
        ->get(route('admin.social-links.index'))
        ->assertOk()
        ->assertSee('Add social media link');

    $this->get(route('admin.social-links.create'))
        ->assertOk()
        ->assertSee('Profile URL')
        ->assertSee('Social media logo');
});

it('creates social links with an uploaded logo and renders clickable footer icons', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $logo = UploadedFile::fake()->createWithContent(
        'telegram.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
    );

    $this->actingAs($admin)
        ->post(route('admin.social-links.store'), [
            'name' => 'Telegram',
            'url' => 'https://t.me/meoxstore',
            'logo' => $logo,
            'sort_order' => 2,
            'is_active' => '1',
        ])
        ->assertRedirectToRoute('admin.social-links.index');

    $socialLink = SocialLink::query()->sole();
    $this->assertModelExists($socialLink);
    Storage::disk('public')->assertExists($socialLink->logo_path);

    $this->get(route('home'))
        ->assertSee('https://t.me/meoxstore')
        ->assertSee(Storage::disk('public')->url($socialLink->logo_path))
        ->assertSee('aria-label="Telegram"', false);
});

it('rejects unsafe social URLs without storing an uploaded logo', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $logo = UploadedFile::fake()->createWithContent(
        'telegram.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
    );

    $this->actingAs($admin)
        ->post(route('admin.social-links.store'), [
            'name' => 'Telegram',
            'url' => 'javascript:alert(1)',
            'logo' => $logo,
            'sort_order' => 0,
            'is_active' => '1',
        ])
        ->assertInvalid(['url' => 'valid URL']);

    $this->assertDatabaseCount('social_links', 0);
    Storage::disk('public')->assertDirectoryEmpty('social-link-logos');
});

it('rejects non-image social logos', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $logo = UploadedFile::fake()->create('telegram.pdf', 20, 'application/pdf');

    $this->actingAs($admin)
        ->post(route('admin.social-links.store'), [
            'name' => 'Telegram',
            'url' => 'https://t.me/meoxstore',
            'logo' => $logo,
            'sort_order' => 0,
            'is_active' => '1',
        ])
        ->assertInvalid(['logo']);

    $this->assertDatabaseCount('social_links', 0);
    Storage::disk('public')->assertDirectoryEmpty('social-link-logos');
});

it('replaces and deletes social logos and hides inactive links', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $initialLogo = UploadedFile::fake()->createWithContent(
        'discord.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
    );

    $this->actingAs($admin)
        ->post(route('admin.social-links.store'), [
            'name' => 'Discord',
            'url' => 'https://discord.gg/meoxstore',
            'logo' => $initialLogo,
            'sort_order' => 1,
            'is_active' => '1',
        ])
        ->assertRedirectToRoute('admin.social-links.index');

    $socialLink = SocialLink::query()->sole();
    $oldLogoPath = $socialLink->logo_path;
    $replacementLogo = UploadedFile::fake()->createWithContent(
        'discord-new.png',
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lscAAAAASUVORK5CYII='),
    );

    $this->put(route('admin.social-links.update', $socialLink), [
        'name' => 'Discord Community',
        'url' => 'https://discord.gg/new-meoxstore',
        'logo' => $replacementLogo,
        'sort_order' => 3,
        'is_active' => '0',
    ])->assertRedirectToRoute('admin.social-links.index');

    $socialLink->refresh();
    expect($socialLink->name)->toBe('Discord Community')
        ->and($socialLink->is_active)->toBeFalse()
        ->and($socialLink->sort_order)->toBe(3);
    Storage::disk('public')->assertMissing($oldLogoPath);
    Storage::disk('public')->assertExists($socialLink->logo_path);

    $this->get(route('home'))
        ->assertDontSee('https://discord.gg/new-meoxstore');

    $newLogoPath = $socialLink->logo_path;

    $this->delete(route('admin.social-links.destroy', $socialLink))
        ->assertRedirectToRoute('admin.social-links.index');

    $this->assertDatabaseMissing('social_links', ['id' => $socialLink->id]);
    Storage::disk('public')->assertMissing($newLogoPath);
});
