<?php

namespace Tests\Feature\Storefront;

use App\Models\SiteSetting;
use App\Models\User;
use App\Modules\Inventory\Models\Inventory;
use App\Modules\Product\Models\Category;
use App\Modules\Product\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Support\Appearance;
use App\Support\HomepageDesigns;
use App\Support\HomepageSections;
use Tests\Feature\Identity\IdentityTestCase;

class HomepageSectionTest extends IdentityTestCase
{
    public function test_default_homepage_sections_render_in_order_with_catalog_data(): void
    {
        $this->seedCatalog();

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="home-hero"',
                'id="home-categories"',
                'id="home-spotlight"',
                'id="popular"',
                'id="vendors"',
                'id="home-benefits"',
            ], false)
            ->assertSee('Nexon Featured Phone')
            ->assertSee('Verified Merchant')
            ->assertSee('Audio & Gadgets')
            ->assertSee(e(HomepageSections::defaults()['hero']['image_url']), false);
    }

    public function test_admin_can_reorder_customize_and_change_every_section_layout(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seedCatalog();
        $sections = HomepageSections::defaults();
        $sections['categories'] = [
            ...$sections['categories'],
            'position' => 0,
            'layout' => 'circles',
            'eyebrow' => 'Featured collections',
            'title' => 'Explore <script>alert("heading")</script>',
            'description' => 'Browse our handpicked category collections.',
            'button_label' => 'Shop collections',
        ];
        $sections['hero']['position'] = 1;
        $sections['hero']['layout'] = 'centered';
        $sections['hero']['image_url'] = 'https://images.example.test/hero.jpg';
        $sections['products']['position'] = 2;
        $sections['products']['layout'] = 'editorial';
        $sections['spotlight']['position'] = 3;
        $sections['spotlight']['layout'] = 'editorial';
        $sections['vendors']['position'] = 4;
        $sections['vendors']['layout'] = 'list';
        $sections['benefits']['position'] = 5;
        $sections['benefits']['layout'] = 'strip';
        $sections['benefits']['button_label'] = 'Start shopping';

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->settingsPayload($sections))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status', 'Site settings updated.');

        $this->assertSame(
            ['categories', 'hero', 'products', 'spotlight', 'vendors', 'benefits'],
            array_keys(SiteSetting::current()->homepageSections()),
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="home-categories"',
                'id="home-hero"',
                'id="popular"',
                'id="home-spotlight"',
                'id="vendors"',
                'id="home-benefits"',
            ], false)
            ->assertSee('data-layout="circles"', false)
            ->assertSee('data-layout="centered"', false)
            ->assertSee('data-layout="editorial"', false)
            ->assertSee('data-layout="list"', false)
            ->assertSee('data-layout="strip"', false)
            ->assertSee('src="https://images.example.test/hero.jpg"', false)
            ->assertSee('Shop collections')
            ->assertSee(e($sections['categories']['title']), false)
            ->assertDontSee('<script>alert("heading")</script>', false);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Homepage sections')
            ->assertSee('name="homepage_sections[categories][title]"', false)
            ->assertSee(e($sections['categories']['title']), false);
    }

    public function test_admin_can_hide_homepage_sections_without_affecting_other_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seedCatalog();
        SiteSetting::current()->update(['site_name' => 'Configured Store']);
        $sections = HomepageSections::defaults();
        $sections['hero']['enabled'] = false;
        $sections['spotlight']['enabled'] = false;
        $sections['vendors']['enabled'] = false;

        $payload = $this->settingsPayload($sections);
        $payload['site_name'] = 'Configured Store';

        $this->actingAs($admin)->put(route('admin.settings.update'), $payload)
            ->assertRedirect(route('admin.settings.edit'));

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="home-hero"', false)
            ->assertDontSee('id="home-spotlight"', false)
            ->assertDontSee('id="vendors"', false)
            ->assertSee('id="home-categories"', false)
            ->assertSee('id="popular"', false)
            ->assertSee('id="home-benefits"', false)
            ->assertSee('Configured Store - Curated Tech & Lifestyle Marketplace');
    }

    public function test_invalid_homepage_settings_are_rejected_and_old_input_is_escaped(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $sections = HomepageSections::defaults();
        $sections['hero']['layout'] = 'unknown';
        $sections['hero']['image_url'] = 'javascript:alert(1)';
        $sections['hero']['title'] = '<script>alert("title")</script>';
        $sections['products']['position'] = 99;
        $sections['products']['description'] = str_repeat('d', 501);
        $sections['vendors']['enabled'] = 'maybe';

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $this->settingsPayload($sections))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors([
                'homepage_sections.hero.layout',
                'homepage_sections.hero.image_url',
                'homepage_sections.products.position',
                'homepage_sections.products.description',
                'homepage_sections.vendors.enabled',
            ]);

        $this->assertNull(SiteSetting::current()->homepage_sections);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('value="javascript:alert(1)"', false)
            ->assertSee(e($sections['hero']['title']), false)
            ->assertDontSee('<script>alert("title")</script>', false);
    }

    public function test_sanitizer_ignores_unknown_sections_and_normalizes_unsafe_stored_values(): void
    {
        $sections = HomepageSections::sanitize([
            'hero' => [
                'enabled' => '0',
                'position' => 99,
                'layout' => 'unknown',
                'title' => '   ',
                'button_label' => str_repeat('b', 70),
                'image_url' => 'javascript:alert(1)',
            ],
            'categories' => [
                'enabled' => 'yes',
                'position' => 0,
                'title' => 123,
                'description' => str_repeat('d', 501),
            ],
            'benefits' => [
                'position' => -10,
                'layout' => 'strip',
            ],
            '../../untrusted' => [
                'enabled' => true,
                'position' => 0,
            ],
        ]);

        $this->assertSame(['categories', 'benefits', 'spotlight', 'products', 'vendors', 'hero'], array_keys($sections));
        $this->assertSame(range(0, 5), array_column($sections, 'position'));
        $this->assertArrayNotHasKey('../../untrusted', $sections);
        $this->assertFalse($sections['hero']['enabled']);
        $this->assertSame('split', $sections['hero']['layout']);
        $this->assertSame(HomepageSections::defaults()['hero']['title'], $sections['hero']['title']);
        $this->assertSame(str_repeat('b', 60), $sections['hero']['button_label']);
        $this->assertSame('', $sections['hero']['image_url']);
        $this->assertTrue($sections['categories']['enabled']);
        $this->assertSame(HomepageSections::defaults()['categories']['title'], $sections['categories']['title']);
        $this->assertSame(str_repeat('d', 500), $sections['categories']['description']);
        $this->assertSame('strip', $sections['benefits']['layout']);
    }

    public function test_admin_can_switch_to_bazaar_design_without_losing_custom_content(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->seedCatalog();
        $sections = HomepageSections::defaults();
        $sections['categories']['title'] = 'Curated pantry collections';
        $sections['categories']['description'] = 'Everyday essentials from approved merchants.';
        $sections['categories']['position'] = 4;
        $sections['benefits']['position'] = 0;

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->settingsPayload($sections, 'bazaar'))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status', 'Site settings updated.');

        $settings = SiteSetting::current();
        $configuredSections = $settings->homepageSections();
        $this->assertSame('bazaar', $settings->homepageDesign());

        foreach (HomepageDesigns::presets()['bazaar']['layouts'] as $key => $layout) {
            $this->assertSame($layout, $configuredSections[$key]['layout']);
        }

        $this->assertSame('Curated pantry collections', $configuredSections['categories']['title']);
        $this->assertSame(4, $configuredSections['categories']['position']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-homepage-design="bazaar"', false)
            ->assertSee('data-layout="mosaic"', false)
            ->assertSee('data-layout="rail"', false)
            ->assertSee('data-layout="bestsellers"', false)
            ->assertSee('data-layout="shelf"', false)
            ->assertSee('data-layout="logos"', false)
            ->assertSee('data-layout="strip"', false)
            ->assertSee('Curated pantry collections')
            ->assertSee('Everyday essentials from approved merchants.');

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Bazaar marketplace')
            ->assertSee('name="homepage_design"', false)
            ->assertSee('value="bazaar"', false);
    }

    public function test_editorial_design_renders_campaign_and_story_layouts(): void
    {
        $this->seedCatalog();
        SiteSetting::current()->update([
            'homepage_design' => 'editorial',
            'homepage_sections' => HomepageDesigns::applyLayouts(HomepageSections::defaults(), 'editorial'),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-homepage-design="editorial"', false)
            ->assertSee('data-layout="campaign"', false)
            ->assertSee('data-layout="tiles"', false)
            ->assertSee('data-layout="story"', false)
            ->assertSee('data-layout="editorial"', false)
            ->assertSee('data-layout="list"', false)
            ->assertSee('data-layout="minimal"', false);
    }

    public function test_invalid_homepage_design_is_rejected_and_legacy_payload_preserves_current_design(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SiteSetting::current()->update(['homepage_design' => 'bazaar']);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $this->settingsPayload(HomepageSections::defaults(), 'unknown'))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors('homepage_design');

        $this->assertSame('bazaar', SiteSetting::current()->homepageDesign());

        $sections = HomepageSections::defaults();
        $sections['products']['title'] = 'Legacy compatible product heading';

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->settingsPayload($sections))
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertSame('bazaar', SiteSetting::current()->homepageDesign());
        $this->assertSame('Legacy compatible product heading', SiteSetting::current()->homepageSections()['products']['title']);
    }

    public function test_unknown_stored_homepage_design_falls_back_to_signature(): void
    {
        SiteSetting::current()->update(['homepage_design' => '../../untrusted']);

        $this->assertSame('signature', SiteSetting::current()->homepageDesign());
        $this->assertSame('signature', HomepageDesigns::sanitize(null));
    }

    private function settingsPayload(array $sections, ?string $design = null): array
    {
        $payload = [
            'site_name' => 'NEXTON',
            'copyright_text' => 'Nexton BD Tech E-Commerce Ltd. All rights reserved.',
            ...Appearance::defaults(),
            'homepage_sections' => $sections,
        ];

        if ($design !== null) {
            $payload['homepage_design'] = $design;
        }

        return $payload;
    }

    private function seedCatalog(): void
    {
        $owner = User::factory()->create();
        $vendor = Vendor::create([
            'owner_user_id' => $owner->id,
            'vendor_code' => 'VERIFIED-01',
            'business_name' => 'Verified Merchant',
            'district' => 'Dhaka',
            'verification_status' => 'approved',
            'status' => 'active',
        ]);
        $category = Category::create([
            'name' => 'Audio & Gadgets',
            'slug' => 'audio-gadgets',
            'is_active' => true,
        ]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'name' => 'Nexon Featured Phone',
            'slug' => 'nexon-featured-phone',
            'price' => 24999,
            'status' => 'approved',
            'is_active' => true,
        ]);
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 12,
            'low_stock_threshold' => 3,
        ]);
    }
}
