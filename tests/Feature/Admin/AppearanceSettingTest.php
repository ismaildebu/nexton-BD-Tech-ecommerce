<?php

namespace Tests\Feature\Admin;

use App\Models\SiteSetting;
use App\Models\User;
use App\Support\Appearance;
use Tests\Feature\Identity\IdentityTestCase;

class AppearanceSettingTest extends IdentityTestCase
{
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'site_name' => 'NEXTON',
            'copyright_text' => 'Nexton BD Tech E-Commerce Ltd. All rights reserved.',
            ...Appearance::defaults(),
        ], $overrides);
    }

    public function test_admin_editor_exposes_presets_and_safe_appearance_controls(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Design preset')
            ->assertSee('Signature orange')
            ->assertSee('Fresh emerald')
            ->assertSee('Primary color')
            ->assertSee('Font family')
            ->assertSee('Header style')
            ->assertSee('Footer style')
            ->assertSee('Live preview')
            ->assertSee('name="primary_color"', false)
            ->assertSee('--color-brand-500: #ff5b22', false);
    }

    public function test_admin_can_publish_a_custom_appearance_across_shared_layouts(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        SiteSetting::current();

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->validPayload([
            'appearance_preset' => 'custom',
            'primary_color' => '#0F766E',
            'accent_color' => '#B45309',
            'neutral_color' => '#475569',
            'font_family' => 'inter',
            'border_radius' => 0,
            'container_width' => 1120,
            'density' => 'compact',
            'header_style' => 'centered',
            'footer_style' => 'dark',
            'card_style' => 'minimal',
        ]))->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHas('status', 'Site settings updated.');

        $this->assertDatabaseHas('site_settings', [
            'appearance_preset' => 'custom',
            'primary_color' => '#0f766e',
            'accent_color' => '#b45309',
            'neutral_color' => '#475569',
            'font_family' => 'inter',
            'border_radius' => 0,
            'container_width' => 1120,
            'density' => 'compact',
            'header_style' => 'centered',
            'footer_style' => 'dark',
            'card_style' => 'minimal',
        ]);
        $this->assertSame('#0f766e', SiteSetting::current()->primary_color);

        $expected = [
            '--color-brand-500: #0f766e',
            '--color-accent-500: #b45309',
            '--color-slate-500: #475569',
            '--container-width: 1120px',
            '--radius-control: 0px',
            '--space-section: 1.75rem',
            '--card-shadow: none',
        ];

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-header-style="centered"', false)
            ->assertSee('data-footer-style="dark"', false)
            ->assertSee('family=Inter', false)
            ->assertSee('family=Noto+Sans+Bengali', false);

        foreach ($expected as $token) {
            $this->get(route('home'))->assertSee($token, false);
        }

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->view('layouts.guest')->assertSee('--color-brand-500: #0f766e', false);
        $this->view('layouts.vendor', ['vendor' => null])->assertSee('--color-brand-500: #0f766e', false);
        $this->view('layouts.app')->assertSee('--color-brand-500: #0f766e', false);

        foreach ($expected as $token) {
            $this->view('layouts.guest')->assertSee($token, false);
            $this->view('layouts.vendor', ['vendor' => null])->assertSee($token, false);
            $this->view('layouts.app')->assertSee($token, false);
        }
    }

    public function test_invalid_or_injectable_appearance_values_are_rejected(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), $this->validPayload([
                'appearance_preset' => 'unknown',
                'primary_color' => '#fff; position:fixed',
                'accent_color' => 'javascript:alert(1)',
                'neutral_color' => '#12345',
                'font_family' => 'evil-font',
                'border_radius' => 25,
                'container_width' => 2000,
                'density' => 'dense',
                'header_style' => 'unknown',
                'footer_style' => 'unknown',
                'card_style' => 'unknown',
            ]))
            ->assertRedirect(route('admin.settings.edit'))
            ->assertSessionHasErrors([
                'appearance_preset',
                'primary_color',
                'accent_color',
                'neutral_color',
                'font_family',
                'border_radius',
                'container_width',
                'density',
                'header_style',
                'footer_style',
                'card_style',
            ]);

        $this->assertDatabaseHas('site_settings', [
            'primary_color' => '#ff5b22',
            'font_family' => 'jakarta',
            'border_radius' => 12,
            'container_width' => 1280,
        ]);

        $this->actingAs($admin)->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('value="#fff; position:fixed"', false)
            ->assertDontSee('style="position:fixed', false)
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_system_font_and_minimal_chrome_are_supported_without_external_font_css(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->put(route('admin.settings.update'), $this->validPayload([
            'appearance_preset' => 'mono',
            'font_family' => 'system',
            'header_style' => 'minimal',
            'footer_style' => 'minimal',
        ]))->assertRedirect(route('admin.settings.edit'));

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-header-style="minimal"', false)
            ->assertSee('data-footer-style="minimal"', false)
            ->assertSee('--font-sans: ui-sans-serif, system-ui', false)
            ->assertDontSee('fonts.googleapis.com', false);
    }

    public function test_token_generation_falls_back_safely_and_keeps_light_primary_buttons_readable(): void
    {
        $tokens = Appearance::tokens([
            'primary_color' => '#ffffff',
            'accent_color' => 'url(https://example.test)',
            'neutral_color' => '#12345',
            'font_family' => 'unknown',
            'header_style' => 'unknown',
        ]);

        $this->assertSame('#7558e8', $tokens['--color-accent-500']);
        $this->assertSame('#64748b', $tokens['--color-slate-500']);
        $this->assertNotSame('#ffffff', $tokens['--color-brand-500']);
        $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $tokens['--color-brand-500']);
        $this->assertStringContainsString('Plus Jakarta Sans', $tokens['--font-sans']);
        $this->assertStringNotContainsString('url(', $tokens['--font-sans']);
    }
}
