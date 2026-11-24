<?php

namespace App\Support;

class HomepageDesigns
{
    private const PRESETS = [
        'signature' => [
            'label' => 'Signature marketplace',
            'description' => 'Premium split banners, balanced category cards and curated product grids.',
            'preview' => ['background' => '#181f2c', 'surface' => '#fff3ed', 'accent' => '#ff5b22'],
            'layouts' => [
                'hero' => 'split',
                'categories' => 'cards',
                'spotlight' => 'horizontal',
                'products' => 'grid',
                'vendors' => 'grid',
                'benefits' => 'cards',
            ],
        ],
        'bazaar' => [
            'label' => 'Bazaar marketplace',
            'description' => 'Promotional banner mosaic, category rail, bestseller duo and compact product shelves.',
            'preview' => ['background' => '#7c2d12', 'surface' => '#fff7ed', 'accent' => '#f97316'],
            'layouts' => [
                'hero' => 'mosaic',
                'categories' => 'rail',
                'spotlight' => 'bestsellers',
                'products' => 'shelf',
                'vendors' => 'logos',
                'benefits' => 'strip',
            ],
        ],
        'editorial' => [
            'label' => 'Editorial showcase',
            'description' => 'Campaign typography, asymmetric collections and a story-led product showcase.',
            'preview' => ['background' => '#1c1917', 'surface' => '#f5f5f4', 'accent' => '#d6d3d1'],
            'layouts' => [
                'hero' => 'campaign',
                'categories' => 'tiles',
                'spotlight' => 'story',
                'products' => 'editorial',
                'vendors' => 'list',
                'benefits' => 'minimal',
            ],
        ],
    ];

    public static function presets(): array
    {
        return self::PRESETS;
    }

    public static function sanitize(mixed $value): string
    {
        return is_string($value) && array_key_exists($value, self::PRESETS) ? $value : 'signature';
    }

    public static function applyLayouts(array $sections, string $design): array
    {
        $design = self::sanitize($design);
        $sections = HomepageSections::sanitize($sections);

        foreach (self::PRESETS[$design]['layouts'] as $key => $layout) {
            if (isset($sections[$key])) {
                $sections[$key]['layout'] = $layout;
            }
        }

        return HomepageSections::sanitize($sections);
    }
}
