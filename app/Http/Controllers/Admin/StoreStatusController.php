<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StoreStatusController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_online' => ['required', 'boolean'],
        ]);

        StoreSetting::current()->update([
            'is_online' => $validated['is_online'],
        ]);

        return back()->with('success', __('store.store_status_updated'));
    }
}
