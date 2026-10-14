<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Optional demo data for the My Account pages: a delivery address, three orders in
 * different delivery stages and their transactions, for one customer.
 *
 *   php artisan db:seed --class=AccountDemoSeeder                    (latest registered user)
 *   ACCOUNT_DEMO_EMAIL=you@example.com php artisan db:seed --class=AccountDemoSeeder
 */
class AccountDemoSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ACCOUNT_DEMO_EMAIL');
        $user = $email ? User::where('email', $email)->first() : User::latest('id')->first();

        if (! $user) {
            $this->command?->warn('No user found. Register an account first.');

            return;
        }

        $address = $user->addresses()->firstOrCreate(['line1' => '12, Rose Apartments, MG Road'], [
            'label' => 'Home',
            'full_name' => $user->name,
            'phone' => $user->phone,
            'line2' => 'Near City Mall',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380009',
            'is_default' => ! $user->addresses()->where('is_default', true)->exists(),
        ]);

        $orders = [
            // [days ago, status, payment, items: [name, category, type, qty, price]]
            [2, 'shipped', 'UPI', [
                ['Vitamin C Brightening Serum', 'Skin Care', 'Serums', 1, 899],
                ['SPF 50 Sunscreen Lotion', 'Skin Care', 'Sunscreens', 2, 499],
            ]],
            [12, 'delivered', 'Card', [
                ['Keratin Smooth Shampoo', 'Hair Care', 'Shampoos', 1, 549],
                ['Argan Oil Conditioner', 'Hair Care', 'Conditioners', 1, 599],
            ]],
            [30, 'cancelled', 'Cash on Delivery', [
                ['Matte Liquid Lipstick', 'Makeup', 'Lipsticks', 1, 399],
            ]],
        ];

        foreach ($orders as $i => [$daysAgo, $status, $payment, $items]) {
            $placed = now()->subDays($daysAgo)->setTime(11, 30);
            $subtotal = collect($items)->sum(fn ($item) => $item[3] * $item[4]);
            $shipping = $subtotal >= 499 ? 0 : 49;
            $number = 'TP' . $placed->format('ymd') . str_pad((string) ($user->id * 10 + $i), 4, '0', STR_PAD_LEFT);

            $order = $user->orders()->firstOrCreate(['order_number' => $number], [
                'status' => $status,
                'subtotal' => $subtotal,
                'shipping' => $shipping,
                'total' => $subtotal + $shipping,
                'payment_method' => $payment,
                'shipping_name' => $address->full_name,
                'shipping_phone' => $address->phone,
                'shipping_address' => $address->oneLine(),
                'courier' => $status === 'cancelled' ? null : 'BlueDart',
                'tracking_number' => $status === 'cancelled' ? null : 'BD' . random_int(100000000, 999999999),
                'expected_delivery' => $placed->copy()->addDays(5),
                'packed_at' => $status !== 'cancelled' ? $placed->copy()->addHours(6) : null,
                'shipped_at' => in_array($status, ['shipped', 'delivered']) ? $placed->copy()->addDay() : null,
                'out_for_delivery_at' => $status === 'delivered' ? $placed->copy()->addDays(3) : null,
                'delivered_at' => $status === 'delivered' ? $placed->copy()->addDays(3)->addHours(5) : null,
                'cancelled_at' => $status === 'cancelled' ? $placed->copy()->addHours(2) : null,
            ]);
            $order->forceFill(['created_at' => $placed, 'updated_at' => $placed])->save();

            if ($order->wasRecentlyCreated) {
                foreach ($items as [$name, $category, $type, $qty, $price]) {
                    $order->items()->create(['product_name' => $name, 'category' => $category, 'type' => $type, 'quantity' => $qty, 'price' => $price]);
                }
                if ($payment !== 'Cash on Delivery') {
                    $user->transactions()->create([
                        'order_id' => $order->id,
                        'reference' => 'TXN' . $placed->format('ymdHis') . $i,
                        'method' => $payment,
                        'amount' => $order->total,
                    ])->forceFill(['created_at' => $placed])->save();
                }
            }
        }

        $this->command?->info("Demo account data added for {$user->email}.");
    }
}
