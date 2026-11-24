<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Coupon\Models\Coupon;
use App\Modules\Identity\Enums\UserStatus;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Notification\Models\Notification;
use App\Modules\Order\Models\Order;
use App\Modules\Payment\Models\Payment;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Review\Models\Review;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        $this->createUser('admin@example.com', 'Admin', true, $password);
        $vendorUser = $this->createUser('vendor@example.com', 'Vendor User', false, $password);
        $customer = $this->createUser('customer@example.com', 'Customer', false, $password);

        $vendor = Vendor::firstOrCreate(['owner_user_id' => $vendorUser->id], [
            'vendor_code' => 'VND-DEMO01',
            'business_name' => 'Demo Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);

        $category = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);

        for ($i = 1; $i <= 10; $i++) {
            $price = 1000 + ($i * 100);
            $product = Product::firstOrCreate(['slug' => "demo-product-{$i}"], [
                'vendor_id' => $vendor->id,
                'category_id' => $category->id,
                'name' => "Demo Product {$i}",
                'price' => $price,
                'status' => 'approved',
                'is_active' => true,
            ]);

            Inventory::firstOrCreate(['product_id' => $product->id], ['quantity' => 10 + $i, 'low_stock_threshold' => 3]);

            $order = Order::firstOrCreate(['order_number' => "ORD-DEMO{$i}"], [
                'user_id' => $customer->id,
                'subtotal' => $price,
                'shipping_total' => 0,
                'discount_total' => 0,
                'total' => $price,
                'status' => 'pending',
                'payment_status' => 'unpaid',
            ]);

            $order->items()->firstOrCreate(['product_id' => $product->id], [
                'vendor_id' => $vendor->id,
                'name' => $product->name,
                'unit_price' => $price,
                'quantity' => 1,
                'line_total' => $price,
            ]);

            Payment::firstOrCreate(['order_id' => $order->id], [
                'vendor_id' => $vendor->id,
                'amount' => $price,
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

        Notification::firstOrCreate([
            'notifiable_type' => $customer->getMorphClass(),
            'notifiable_id' => $customer->id,
            'type' => 'info',
        ], [
            'id' => (string) Str::uuid(),
            'data' => ['msg' => 'Welcome'],
        ]);
    }

    private function createUser(string $email, string $name, bool $isAdmin, string $password): User
    {
        $user = User::firstOrCreate(['email' => $email], [
            'name' => $name,
            'password' => $password,
        ]);

        $user->forceFill([
            'status' => UserStatus::Active,
            'is_admin' => $isAdmin,
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }
}
