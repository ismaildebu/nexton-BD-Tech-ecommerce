<?php

namespace Tests\Feature\Identity;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class IdentityTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Views use @vite(); tests must not depend on a built asset manifest.
        $this->withoutVite();
    }
}
