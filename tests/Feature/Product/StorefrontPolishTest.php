<?php

namespace Tests\Feature\Product;

use App\Models\User;
use App\Modules\Cms\Models\CmsPage;
use Tests\Feature\Identity\IdentityTestCase;

class StorefrontPolishTest extends IdentityTestCase
{
    public function test_frontend_renders_only_published_cms_footer_links(): void
    {
        $published = CmsPage::create(['title' => 'Published Privacy', 'slug' => 'published-privacy', 'content' => 'Privacy', 'is_published' => true]);
        CmsPage::create(['title' => 'Draft Policy', 'slug' => 'draft-policy', 'content' => 'Draft', 'is_published' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Published Privacy')
            ->assertSee('href="'.route('cms.show', $published->slug).'"', false)
            ->assertDontSee('Draft Policy');
    }

    public function test_frontend_displays_checkout_style_session_errors(): void
    {
        $this->withSession(['error' => 'Checkout could not be completed.'])
            ->get(route('cart.index'))
            ->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->withSession(['error' => 'Checkout could not be completed.'])
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Checkout could not be completed.');
    }

    public function test_cart_validation_errors_are_displayed_by_frontend_layout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('cart.index'))
            ->post(route('cart.store'), ['product_id' => 999999, 'quantity' => 1])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHasErrors('product_id');

        $this->actingAs($user)->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Please correct the following errors:')
            ->assertSee('The selected product id is invalid.');
    }

    public function test_forgot_password_page_has_a_visible_phone_reset_link(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Reset using a phone code')
            ->assertSee('href="'.route('password.phone').'"', false);
    }
}
