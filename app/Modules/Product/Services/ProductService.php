<?php

namespace App\Modules\Product\Services;

use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\VendorVerificationStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function create(array $data): Product
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(6));
        }

        return DB::transaction(fn () => Product::create($data));
    }

    /**
     * Only whitelisted fields can change. vendor_id, status and is_active never come from vendor input.
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $locked = Product::whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            $changes = Arr::only($data, ['name', 'description', 'price', 'category_id', 'brand_id']);

            if ($locked->status === 'approved') {
                $changes['status'] = 'pending';
                $changes['is_active'] = false;
            }

            $locked->update($changes);

            return $locked->refresh();
        });
    }

    public function archive(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $locked = Product::whereKey($product->getKey())->lockForUpdate()->firstOrFail();
            $locked->update(['is_active' => false]);
        });
    }

    /**
     * @throws \DomainException when the product cannot be approved.
     */
    public function approve(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new \DomainException(__('Only pending products can be approved.'));
            }

            if ($locked->vendor?->verification_status !== VendorVerificationStatus::Approved
                || $locked->vendor?->status !== VendorStatus::Active) {
                throw new \DomainException(__('The vendor is not active and approved, so this product cannot go live.'));
            }

            $locked->update(['status' => 'approved', 'is_active' => true]);
        });
    }

    /**
     * @throws \DomainException when the product cannot be rejected.
     */
    public function reject(Product $product): void
    {
        DB::transaction(function () use ($product) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'pending') {
                throw new \DomainException(__('Only pending products can be rejected.'));
            }

            $locked->update(['status' => 'rejected', 'is_active' => false]);
        });
    }
}