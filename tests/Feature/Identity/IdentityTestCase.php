<?php

namespace Tests\Feature\Identity;

use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class IdentityTestCase extends TestCase
{
    use RefreshDatabase;

    protected function htmlXPath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $document->loadHTML($html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Views use @vite(); tests must not depend on a built asset manifest.
        $this->withoutVite();
    }
}
