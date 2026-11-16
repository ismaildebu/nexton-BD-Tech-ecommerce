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
            ->post(route('cms.store'), ['title' => 'About', 'slug' => 'about', 'content' => 'Hello', 'is_published' => true])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('cms_pages', ['slug' => 'about']);
    }
}
