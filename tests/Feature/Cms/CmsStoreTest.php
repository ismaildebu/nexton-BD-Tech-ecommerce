<?php

namespace Tests\Feature\Cms;

use App\Models\User;
use Tests\Feature\Identity\IdentityTestCase;

class CmsStoreTest extends IdentityTestCase
{
    public function test_admin_can_create_page(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.cms.create'))
            ->post(route('cms.store'), ['title' => 'About', 'slug' => 'About Us!', 'content' => 'Hello', 'is_published' => true])
            ->assertRedirect(route('admin.cms.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('cms_pages', ['slug' => 'about-us', 'is_published' => true]);
        $this->actingAs($admin)->get(route('cms.show', 'about-us'))
            ->assertOk()
            ->assertSee('Hello');
    }

    public function test_admin_cms_form_is_available_and_invalid_slug_has_visible_errors(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.cms.create'))
            ->assertOk()
            ->assertSee('Create page');

        $this->actingAs($admin)->from(route('admin.cms.create'))
            ->post(route('cms.store'), ['title' => 'Invalid', 'slug' => '!!!', 'content' => 'Hello'])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('cms_pages', 0);
    }

    public function test_unpublished_page_is_not_public_and_non_admin_cannot_create_one(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $customer = User::factory()->create();

        $this->actingAs($admin)->post(route('cms.store'), [
            'title' => 'Draft', 'slug' => 'draft', 'content' => 'Private', 'is_published' => false,
        ])->assertRedirect(route('admin.cms.index'));

        $this->get(route('cms.show', 'draft'))->assertNotFound();
        $this->actingAs($customer)->post(route('cms.store'), [
            'title' => 'No', 'slug' => 'no', 'content' => 'No',
        ])->assertForbidden();
    }
}
