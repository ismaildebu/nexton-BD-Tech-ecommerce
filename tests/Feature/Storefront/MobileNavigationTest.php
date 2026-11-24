<?php

namespace Tests\Feature\Storefront;

use Tests\Feature\Identity\IdentityTestCase;

class MobileNavigationTest extends IdentityTestCase
{
    public function test_mobile_drawer_is_viewport_positioned_outside_the_header_and_scrollable(): void
    {
        $response = $this->get('/')
            ->assertOk()
            ->assertSee('x-ref="storeDrawerOpen"', false)
            ->assertSee('aria-controls="storefront-navigation"', false)
            ->assertSee(':aria-expanded="mobileDrawer.toString()"', false)
            ->assertSee('$refs.storeDrawerClose.focus()', false)
            ->assertSee('@resize.window="if (innerWidth >= 1024) mobileDrawer = false"', false)
            ->assertSeeInOrder(['</header>', 'x-show="mobileDrawer"', 'id="storefront-navigation"', '<main id="storefront-main"']);

        $xpath = $this->htmlXPath($response->getContent());
        $drawer = $xpath->query('//body/*[@id="storefront-navigation"]')->item(0);

        $this->assertNotNull($drawer);
        $this->assertSame('body', $drawer->parentNode->nodeName);
        $this->assertSame(0, $xpath->query('//header//*[@id="storefront-navigation"]')->length);
        $this->assertSame('dialog', $drawer->getAttribute('role'));
        $this->assertSame('true', $drawer->getAttribute('aria-modal'));
        $this->assertSame('-1', $drawer->getAttribute('tabindex'));
        $this->assertStringContainsString(
            'class="fixed inset-0 z-50 bg-ink-900/60 backdrop-blur-sm lg:hidden" x-cloak aria-hidden="true"></div>',
            $response->getContent(),
        );

        $classes = explode(' ', $drawer->getAttribute('class'));
        $this->assertContains('fixed', $classes);
        $this->assertContains('inset-y-0', $classes);
        $this->assertContains('overflow-y-auto', $classes);
        $this->assertContains('overscroll-contain', $classes);
        $this->assertContains('pb-[max(1.5rem,env(safe-area-inset-bottom))]', $classes);
    }
}
