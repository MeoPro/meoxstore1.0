<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SocialLink;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SocialLinkController extends Controller
{
    public function index(): View
    {
        return view('admin.social-links.index', [
            'socialLinks' => SocialLink::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->orderBy('id')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.social-links.form', [
            'socialLink' => new SocialLink(['is_active' => true]),
            'title' => 'Add social media link',
            'action' => route('admin.social-links.store'),
            'method' => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedData($request);
        $logo = $request->file('logo');

        if (! $logo instanceof UploadedFile) {
            throw new RuntimeException('A social media logo is required.');
        }

        $logoPath = $this->storeLogo($logo);
        $validated['logo_path'] = $logoPath;

        try {
            SocialLink::query()->create($validated);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($logoPath);

            throw $exception;
        }

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'Social media link created successfully.');
    }

    public function edit(SocialLink $socialLink): View
    {
        return view('admin.social-links.form', [
            'socialLink' => $socialLink,
            'title' => 'Edit social media link',
            'action' => route('admin.social-links.update', $socialLink),
            'method' => 'PUT',
        ]);
    }

    public function update(Request $request, SocialLink $socialLink): RedirectResponse
    {
        $validated = $this->validatedData($request, $socialLink);
        $previousLogoPath = $socialLink->logo_path;
        $logo = $request->file('logo');
        $newLogoPath = $logo instanceof UploadedFile ? $this->storeLogo($logo) : null;
        $validated['logo_path'] = $newLogoPath ?? $previousLogoPath;

        try {
            $socialLink->update($validated);
        } catch (Throwable $exception) {
            if ($newLogoPath !== null) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath !== null) {
            Storage::disk('public')->delete($previousLogoPath);
        }

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'Social media link updated successfully.');
    }

    public function destroy(SocialLink $socialLink): RedirectResponse
    {
        $logoPath = $socialLink->logo_path;
        $socialLink->delete();
        Storage::disk('public')->delete($logoPath);

        return redirect()
            ->route('admin.social-links.index')
            ->with('success', 'Social media link deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?SocialLink $socialLink = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url:http,https', 'max:2048'],
            'logo' => [$socialLink === null ? 'required' : 'nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
        ]);

        unset($validated['logo']);

        return $validated;
    }

    private function storeLogo(UploadedFile $logo): string
    {
        $logoPath = $logo->store('social-link-logos', 'public');

        if ($logoPath === false) {
            throw new RuntimeException('The social media logo could not be stored.');
        }

        return $logoPath;
    }
}
