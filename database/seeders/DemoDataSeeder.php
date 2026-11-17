<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Review\Models\Review;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Notification\Models\Notification;
use App\Modules\Shipping\Models\Shipping;
use App\Modules\Commission\Models\Commission;
use App\Modules\Accounting\Models\Settlement;
use App\Modules\Subscription\Models\Subscription;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Modules\Identity\Enums\UserStatus;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        $vendorUser = User::firstOrCreate(['email' => 'vendor@example.com'], [
            'name' => 'Vendor User',
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $vendor = Vendor::firstOrCreate(['owner_user_id' => $vendorUser->id], [
            'vendor_code' => 'VND-DEMO01',
            'business_name' => 'Demo Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $customer = User::firstOrCreate(['email' => 'customer@example.com'], [
            'name' => 'Customer',
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $cat = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);

        for ($i = 1; $i <= 10; $i++) {
            $product = Product::firstOrCreate(['slug' => "demo-product-{$i}"], [
                'vendor_id' => $vendor->id,
                'category_id' => $cat->id,
                'name' => "Demo Product {$i}",
                'price' => 1000 + ($i * 100),
                'status' => 'approved',
                'is_active' => true,
            ]);

            Inventory::firstOrCreate(['product_id' => $product->id], ['quantity' => 10 + $i, 'low_stock_threshold' => 3]);

            $order = Order::firstOrCreate(['order_number' => "ORD-DEMO{$i}"], [
                'user_id' => $customer->id,
                'total' => 1000 + ($i * 100),
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            Payment::firstOrCreate(['order_id' => $order->id], [
                'amount' => 1000 + ($i * 100),
                'gateway' => 'bkash',
                'status' => 'pending',
            ]);

            Review::firstOrCreate(['user_id' => $customer->id, 'product_id' => $product->id], [
                'rating' => 5,
                'comment' => 'Great product',
            ]);
        }

        Coupon::firstOrCreate(['code' => 'SAVE10'], [
            'type' => 'percent',
            'value' => 10,
            'is_active' => true,
        ]);

        Notification::firstOrCreate(['id' => (string) Str::uuid()], [
            'type' => 'info',
            'notifiable_type' => get_class($customer),
            'notifiable_id' => $customer->id,
            'data' => ['msg' => 'Welcome'],
        ]);
    }
}
