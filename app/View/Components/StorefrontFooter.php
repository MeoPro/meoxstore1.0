<?php

namespace App\View\Components;

use App\Models\PaymentSetting;
use App\Models\SocialLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class StorefrontFooter extends Component
{
    /**
     * @var Collection<int, PaymentSetting>
     */
    public Collection $paymentMethods;

    public string $supportEmail;

    public string $supportPhone;

    public string $googleMapsProfileUrl;

    /**
     * @var array<int, array{network: string, label: string, icon: string, mark: string, url: string, logo_url: ?string}>
     */
    public array $socialLinks;

    public function __construct(public bool $showTemplateCredit = false)
    {
        $this->paymentMethods = PaymentSetting::query()
            ->where('is_active', true)
            ->whereNotNull('logo_path')
            ->where('logo_path', '!=', '')
            ->orderBy('name')
            ->get(['id', 'name', 'logo_path']);
        $this->supportEmail = (string) config('store.admin_email', '');
        $this->supportPhone = (string) config('store.admin_phone', '');
        $this->googleMapsProfileUrl = (string) config('services.google_maps.profile_url', '');
        $socialLinks = config('store.social_links', []);
        $managedSocialLinks = SocialLink::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'url', 'logo_path', 'is_active']);
        $managedNetworkNames = $managedSocialLinks
            ->map(fn (SocialLink $socialLink): string => Str::slug($socialLink->name))
            ->all();
        $isSafeUrl = static function (mixed $url): bool {
            return is_string($url)
                && filter_var($url, FILTER_VALIDATE_URL) !== false
                && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true);
        };

        $databaseSocialLinks = $managedSocialLinks
            ->where('is_active', true)
            ->map(fn (SocialLink $socialLink): array => [
                'network' => 'custom',
                'label' => $socialLink->name,
                'icon' => '',
                'mark' => '',
                'url' => $socialLink->url,
                'logo_url' => Storage::disk('public')->url($socialLink->logo_path),
            ])
            ->filter(fn (array $socialLink): bool => $isSafeUrl($socialLink['url']))
            ->values();

        $legacySocialLinks = collect([
            ['key' => 'facebook', 'network' => 'facebook', 'label' => 'Facebook', 'icon' => 'fa-facebook-f', 'mark' => 'F'],
            ['key' => 'instagram', 'network' => 'instagram', 'label' => 'Instagram', 'icon' => 'fa-instagram', 'mark' => 'IG'],
            ['key' => 'tiktok', 'network' => 'tiktok', 'label' => 'TikTok', 'icon' => 'fa-tiktok', 'mark' => 'TT'],
            ['key' => 'youtube', 'network' => 'youtube', 'label' => 'YouTube', 'icon' => 'fa-youtube', 'mark' => 'YT'],
            ['key' => 'x', 'network' => 'x', 'label' => 'X', 'icon' => 'fa-twitter', 'mark' => 'X'],
            ['key' => 'threads', 'network' => 'threads', 'label' => 'Threads', 'icon' => '', 'mark' => '@'],
        ])
            ->map(function (array $socialLink) use ($socialLinks): array {
                $socialLink['url'] = $socialLinks[$socialLink['key']] ?? '';
                $socialLink['logo_url'] = null;
                unset($socialLink['key']);

                return $socialLink;
            })
            ->reject(fn (array $socialLink): bool => in_array(Str::slug($socialLink['label']), $managedNetworkNames, true))
            ->filter(fn (array $socialLink): bool => $isSafeUrl($socialLink['url']))
            ->values();

        $this->socialLinks = $databaseSocialLinks
            ->concat($legacySocialLinks)
            ->values()
            ->all();
    }

    public function render(): View
    {
        return view('components.storefront-footer');
    }
}
