<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Appearance;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Identity\IdentityTestCase;

class SiteSettingTest extends IdentityTestCase
{
    private function validPayload(): array
    {
        return [
            'site_name' => 'Nexon BD',
            'contact_email' => 'support@nexon.test',
            'contact_phone' => '+880 1700-000000',
            'announcement' => 'Free delivery on every order this week.',
            'footer_description' => 'Trusted technology marketplace.',
            'copyright_text' => 'Nexon BD. All rights reserved.',
            ...Appearance::defaults(),
            'remove_logo' => '0',
        ];
    }

    public function test_settings_are_seeded_once_and_linked_from_the_admin_dashboard(): void
    {
        $this->assertDatabaseCount('site_settings', 1);
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(route('admin.settings.edit'), false);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Design system')
            ->assertSee('Live preview')
            ->assertSee('Branding')
            ->assertSee('Announcement')
            ->assertSee('Contact information')
            ->assertSee('Footer')
            ->assertSee('Save settings');

        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_guests_and_customers_cannot_read_or_update_site_settings(): void
    {
        $payload = $this->validPayload();

        $this->get(route('admin.settings.edit'))->assertRedirect(route('login'));
        $this->put(route('admin.settings.update'), $payload)->assertRedirect(route('login'));

        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($customer)->put(route('admin.settings.update'), $payload)->assertForbidden();

        $this->assertDatabaseHas('site_settings', ['site_name' => 'NEXTON']);
    }

    public function test_admin_updates_settings_and_storefront_refreshes_cached_values(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SiteSetting::current();

        $payload = $this->validPayload();
        $payload['id'] = 99;
        $payload['logo_path'] = 'site-settings/untrusted.png';

        $this->actingAs($admin)->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status', 'Site settings updated.');

        $this->assertDatabaseCount('site_settings', 1);
        $this->assertDatabaseHas('site_settings', [
            'id' => SiteSetting::SINGLETON_ID,
            'site_name' => 'Nexon BD',
            'contact_email' => 'support@nexon.test',
            'contact_phone' => '+880 1700-000000',
            'announcement' => 'Free delivery on every order this week.',
            'footer_description' => 'Trusted technology marketplace.',
            'copyright_text' => 'Nexon BD. All rights reserved.',
            'logo_path' => null,
        ]);
        $this->assertSame('Nexon BD', SiteSetting::current()->site_name);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nexon BD - Curated Tech & Lifestyle Marketplace')
            ->assertSee('Free delivery on every order this week.')
            ->assertSee('Trusted technology marketplace.')
            ->assertSee('href="mailto:support@nexon.test"', false)
            ->assertSee('href="tel:+880 1700-000000"', false)
            ->assertSee('Nexon BD. All rights reserved.');
    }

    public function test_invalid_settings_keep_existing_values_and_restore_old_input(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), [
                'site_name' => '',
                'contact_email' => 'not-an-email',
                'announcement' => str_repeat('a', 256),
                'copyright_text' => '',
                'logo' => UploadedFile::fake()->create('logo.txt', 10, 'text/plain'),
            ])
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors(['site_name', 'contact_email', 'announcement', 'copyright_text', 'logo']);

        $this->assertDatabaseHas('site_settings', [
            'site_name' => 'NEXTON',
            'contact_email' => null,
            'announcement' => 'Free nationwide delivery on orders over ৳2,500.',
            'copyright_text' => 'Nexton BD Tech E-Commerce Ltd. All rights reserved.',
        ]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('not-an-email');
    }

    public function test_admin_can_replace_and_remove_the_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $settings = SiteSetting::current();
        Storage::disk('public')->put('site-settings/old-logo.png', 'old logo');
        $settings->update(['logo_path' => 'site-settings/old-logo.png']);

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->validPayload() + [
            'logo' => UploadedFile::fake()->image('new-logo.png', 100, 100),
        ])->assertRedirect(route('admin.settings.edit'));

        $settings = SiteSetting::current();
        $this->assertNotNull($settings->logo_path);
        $this->assertStringStartsWith('site-settings/', $settings->logo_path);
        Storage::disk('public')->assertMissing('site-settings/old-logo.png');
        Storage::disk('public')->assertExists($settings->logo_path);
        $this->assertSame(Storage::disk('public')->url($settings->logo_path), $settings->logo_url);

        $newLogo = $settings->logo_path;

        $this->actingAs($admin)->put(route('admin.settings.update'), array_merge($this->validPayload(), [
            'remove_logo' => '1',
        ]))->assertRedirect(route('admin.settings.edit'));

        $this->assertNull(SiteSetting::current()->logo_path);
        Storage::disk('public')->assertMissing($newLogo);
    }

    public function test_empty_optional_values_hide_announcement_contact_and_footer_description(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $payload = $this->validPayload();
        $payload['announcement'] = null;
        $payload['contact_email'] = null;
        $payload['contact_phone'] = null;
        $payload['footer_description'] = null;

        $this->actingAs($admin)->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Free nationwide delivery on orders over ৳2,500.')
            ->assertDontSee('mailto:support@nexon.test', false)
            ->assertDontSee('tel:+880 1700-000000', false)
            ->assertDontSee('Trusted technology marketplace.')
            ->assertSee('Nexon BD. All rights reserved.');
    }
}
