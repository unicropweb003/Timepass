<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'contact' => config('shop.contact', []),
            'social' => config('shop.social', []),
            'testPayments' => Setting::paymentTestMode(),
        ]);
    }

    /** Turn test payment mode on or off (checkout without paying, for testing). */
    public function updateTestPayments(Request $request)
    {
        $enabled = $request->boolean('enabled');
        Setting::put('payment_test_mode', $enabled);

        return back()->with('status', $enabled
            ? 'Test payments are ON. Customers can place orders without paying.'
            : 'Test payments are OFF.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'contact.email' => ['nullable', 'string', 'max:255'],
            'contact.phone' => ['nullable', 'string', 'max:255'],
            'contact.address' => ['nullable', 'string', 'max:255'],
            'contact.hours' => ['nullable', 'string', 'max:255'],
            'social.instagram' => ['nullable', 'url', 'max:255'],
            'social.facebook' => ['nullable', 'url', 'max:255'],
            'social.youtube' => ['nullable', 'url', 'max:255'],
            'social.x' => ['nullable', 'url', 'max:255'],
        ]);

        Setting::put('contact', array_filter($data['contact'] ?? [], fn ($v) => $v !== null));
        Setting::put('social', $data['social'] ?? []);

        return back()->with('status', 'Site settings saved.');
    }
}
