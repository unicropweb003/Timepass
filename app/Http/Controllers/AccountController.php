<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Support\PerPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * "My Account" pages for logged-in customers. Every query goes through the
 * current user's relations, so nobody can see another customer's data.
 */
class AccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('account.index', [
            'recentOrders' => $user->orders()->withCount('items')->latest()->take(3)->get(),
            'orderCount' => $user->orders()->count(),
            'activeCount' => $user->orders()->whereNotIn('status', ['delivered', 'cancelled'])->count(),
            'totalSpent' => $user->transactions()->where('type', 'payment')->where('status', 'success')->sum('amount'),
            'defaultAddress' => $user->addresses()->orderByDesc('is_default')->first(),
        ]);
    }

    public function orders(Request $request)
    {
        // The order card only shows item names and the total quantity.
        $orders = $request->user()->orders()->with('items:id,order_id,product_name,quantity')->latest()
            ->paginate(PerPage::from($request))->withQueryString();

        return view('account.orders', compact('orders'));
    }

    public function showOrder(Request $request, string $orderNumber)
    {
        $order = $request->user()->orders()->with(['items', 'transactions'])
            ->where('order_number', $orderNumber)->firstOrFail();

        return view('account.order', compact('order'));
    }

    public function transactions(Request $request)
    {
        $transactions = $request->user()->transactions()->with('order:id,order_number')->latest()
            ->paginate(PerPage::from($request))->withQueryString();

        return view('account.transactions', compact('transactions'));
    }

    public function addresses(Request $request)
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->latest()->get();

        return view('account.addresses', compact('addresses'));
    }

    public function storeAddress(Request $request)
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'line1' => ['required', 'string', 'max:255'],
            'line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'regex:/^[0-9]{6}$/'],
        ], [
            'phone.regex' => 'The phone number must be 10 to 15 digits.',
            'pincode.regex' => 'The pincode must be 6 digits.',
        ]);

        $user = $request->user();
        // The first address, or one marked as default, becomes the default.
        $makeDefault = $request->boolean('is_default') || ! $user->addresses()->exists();
        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }
        $user->addresses()->create($data + ['is_default' => $makeDefault]);

        return redirect()->route('account.addresses')->with('status', 'Address saved.');
    }

    public function defaultAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        $request->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return back()->with('status', 'Default address updated.');
    }

    public function destroyAddress(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 404);

        $wasDefault = $address->is_default;
        $address->delete();
        if ($wasDefault) {
            $request->user()->addresses()->latest()->first()?->update(['is_default' => true]);
        }

        return back()->with('status', 'Address removed.');
    }

    public function profile()
    {
        return view('account.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9]{10,15}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ], [
            'phone.regex' => 'The phone number must be 10 to 15 digits.',
        ]);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => $data['password']]);

        return back()->with('status', 'Password changed.');
    }
}
