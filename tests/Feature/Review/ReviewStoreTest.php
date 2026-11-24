<?php

namespace Tests\Feature\Review;

use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Product;
use App\Modules\Review\Models\Review;
use App\Modules\Vendor\Models\Vendor;
use Tests\Feature\Identity\IdentityTestCase;

class ReviewStoreTest extends IdentityTestCase
{
    private function context(): array
    {
        $vendor = Vendor::create([
            'owner_user_id' => User::factory()->create()->id,
            'vendor_code' => 'VND-REV001',
            'business_name' => 'Review Shop',
            'verification_status' => 'approved',
            'status' => 'active',
            'submitted_at' => now(),
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => 'Reviewed Product',
            'slug' => 'p-review',
            'price' => 10,
            'status' => 'approved',
            'is_active' => true,
        ]);
        Inventory::create(['product_id' => $product->id, 'quantity' => 5]);

        return compact('product');
    }

    public function test_user_can_submit_review(): void
    {
        $context = $this->context();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('reviews.store'), ['product_id' => $context['product']->id, 'rating' => 5, 'comment' => 'Good'])
            ->assertRedirect(route('products.show', $context['product']).'#reviews');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'product_id' => $context['product']->id,
            'rating' => 5,
            'comment' => 'Good',
        ]);
    }

    public function test_product_detail_displays_real_review_aggregate_and_list(): void
    {
        $context = $this->context();
        $firstUser = User::factory()->create(['name' => 'Real Customer One']);
        $secondUser = User::factory()->create(['name' => 'Real Customer Two']);
        Review::create(['user_id' => $firstUser->id, 'product_id' => $context['product']->id, 'rating' => 4, 'comment' => 'Real positive feedback']);
        Review::create(['user_id' => $secondUser->id, 'product_id' => $context['product']->id, 'rating' => 2, 'comment' => 'Real mixed feedback']);

        $this->actingAs($firstUser)->get(route('products.show', $context['product']))
            ->assertOk()
            ->assertSee('3.0')
            ->assertSee('2 reviews')
            ->assertSee('Real Customer One')
            ->assertSee('Real Customer Two')
            ->assertSee('Real positive feedback')
            ->assertSee('Real mixed feedback')
            ->assertSee('action="'.route('reviews.store').'"', false)
            ->assertDontSee('Sabbir Ahmed')
            ->assertDontSee('320 Reviews')
            ->assertDontSee('4.8');
    }

    public function test_review_validation_errors_are_shown_on_product_detail(): void
    {
        $context = $this->context();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('products.show', $context['product']))
            ->post(route('reviews.store'), ['product_id' => $context['product']->id, 'rating' => 6, 'comment' => 'Invalid'])
            ->assertRedirect(route('products.show', $context['product']))
            ->assertSessionHasErrors('rating');

        $this->actingAs($user)->get(route('products.show', $context['product']))
            ->assertOk()
            ->assertSee('Please correct the following errors:')
            ->assertSee('The rating field must not be greater than 5.');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_admin_can_delete_review_and_sees_product_and_user_context(): void
    {
        $context = $this->context();
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create(['name' => 'Review Author', 'email' => 'author@example.com']);
        $review = Review::create(['user_id' => $customer->id, 'product_id' => $context['product']->id, 'rating' => 5, 'comment' => 'Moderated review']);

        $this->actingAs($admin)->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Reviewed Product')
            ->assertSee('Review Shop')
            ->assertSee('Review Author')
            ->assertSee('author@example.com')
            ->assertSee('action="'.route('admin.reviews.destroy', $review).'"', false);

        $this->actingAs($admin)->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect(route('admin.reviews.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_non_admin_cannot_delete_reviews_through_admin_route(): void
    {
        $context = $this->context();
        $customer = User::factory()->create();
        $review = Review::create(['user_id' => $customer->id, 'product_id' => $context['product']->id, 'rating' => 5, 'comment' => 'Keep me']);

        $this->actingAs($customer)->delete(route('admin.reviews.destroy', $review))->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
