<?php

namespace App\Support;

class HomepageSections
{
    private const SECTIONS = [
        'hero' => [
            'label' => 'Hero banner',
            'layouts' => [
                'split' => 'Split banner',
                'centered' => 'Centered banner',
                'mosaic' => 'Promotional mosaic',
                'campaign' => 'Editorial campaign',
            ],
            'defaults' => [
                'eyebrow' => 'Curated for everyday life',
                'title' => 'Thoughtfully chosen tech. Remarkably simple shopping.',
                'description' => 'Discover approved products from verified merchants, with secure checkout and dependable support.',
                'button_label' => 'Explore catalog',
                'layout' => 'split',
                'image_url' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=1600&q=85',
            ],
        ],
        'categories' => [
            'label' => 'Categories',
            'layouts' => [
                'cards' => 'Feature cards',
                'circles' => 'Circle icons',
                'rail' => 'Horizontal category rail',
                'tiles' => 'Editorial category tiles',
            ],
            'defaults' => [
                'eyebrow' => 'Find your favorites',
                'title' => 'Shop by category',
                'description' => 'Move through focused collections and find exactly what you need.',
                'button_label' => 'View all products',
                'layout' => 'cards',
            ],
        ],
        'spotlight' => [
            'label' => 'Product spotlight',
            'layouts' => [
                'horizontal' => 'Horizontal feature',
                'editorial' => 'Editorial feature',
                'bestsellers' => 'Bestseller duo',
                'story' => 'Product story',
            ],
            'defaults' => [
                'eyebrow' => "Editor's pick",
                'title' => 'Product spotlight',
                'description' => 'A featured find from our latest approved collection.',
                'button_label' => 'View details',
                'layout' => 'horizontal',
            ],
        ],
        'products' => [
            'label' => 'Popular products',
            'layouts' => [
                'grid' => 'Balanced grid',
                'editorial' => 'Editorial grid',
                'shelf' => 'Horizontal product shelf',
                'compact' => 'Compact product grid',
            ],
            'defaults' => [
                'eyebrow' => 'Handpicked for you',
                'title' => 'Popular products',
                'description' => 'Fresh arrivals and customer favorites from trusted sellers.',
                'button_label' => 'See all products',
                'layout' => 'grid',
            ],
        ],
        'vendors' => [
            'label' => 'Verified merchants',
            'layouts' => [
                'grid' => 'Card grid',
                'list' => 'Compact list',
                'logos' => 'Brand logo wall',
            ],
            'defaults' => [
                'eyebrow' => 'Shop with confidence',
                'title' => 'Verified merchant stores',
                'description' => 'Official authorized dealers and highly rated vendors in Bangladesh.',
                'button_label' => 'Become a seller',
                'layout' => 'grid',
            ],
        ],
        'benefits' => [
            'label' => 'Shopping benefits',
            'layouts' => [
                'cards' => 'Feature cards',
                'strip' => 'Compact strip',
                'minimal' => 'Minimal promise row',
            ],
            'defaults' => [
                'eyebrow' => 'The NEXTON promise',
                'title' => 'A better way to shop online',
                'description' => 'Useful promises, clear policies and support that stays close.',
                'button_label' => '',
                'layout' => 'cards',
            ],
        ],
    ];

    public static function sections(): array
    {
        return self::SECTIONS;
    }

    public static function defaults(): array
    {
        $sections = [];

        foreach (self::SECTIONS as $key => $definition) {
            $sections[$key] = [
                'enabled' => true,
                'position' => count($sections),
                ...$definition['defaults'],
            ];
        }

        return $sections;
    }

    public static function sanitize(array $values): array
    {
        $defaults = self::defaults();
        $sections = [];

        foreach (self::SECTIONS as $key => $definition) {
            $value = is_array($values[$key] ?? null) ? $values[$key] : [];
            $default = $defaults[$key];
            $layout = is_string($value['layout'] ?? null) ? $value['layout'] : $default['layout'];

            $sections[$key] = [
                'enabled' => array_key_exists('enabled', $value)
                    ? filter_var($value['enabled'], FILTER_VALIDATE_BOOLEAN) === true
                    : $default['enabled'],
                'position' => self::position($value['position'] ?? null, $default['position']),
                'eyebrow' => self::text($value['eyebrow'] ?? null, $default['eyebrow'], 80, false),
                'title' => self::text($value['title'] ?? null, $default['title'], 120),
                'description' => self::text($value['description'] ?? null, $default['description'], 500, false),
                'button_label' => self::text($value['button_label'] ?? null, $default['button_label'], 60, false),
                'layout' => array_key_exists($layout, $definition['layouts']) ? $layout : $default['layout'],
            ];

            if ($key === 'hero') {
                $image = array_key_exists('image_url', $value)
                    ? (is_string($value['image_url']) ? trim($value['image_url']) : '')
                    : $default['image_url'];
                $sections[$key]['image_url'] = self::safeUrl($image) ? $image : '';
            }
        }

        uasort($sections, fn (array $left, array $right): int => $left['position'] <=> $right['position']);

        $ordered = [];
        foreach ($sections as $key => $section) {
            $section['position'] = count($ordered);
            $ordered[$key] = $section;
        }

        return $ordered;
    }

    private static function position(mixed $value, int $default): int
    {
        $position = filter_var($value, FILTER_VALIDATE_INT);

        return $position === false ? $default : max(0, min(count(self::SECTIONS) - 1, $position));
    }

    private static function text(mixed $value, string $default, int $limit, bool $fallbackWhenEmpty = true): string
    {
        if (! is_string($value)) {
            return $default;
        }

        $value = trim($value);

        if ($value === '') {
            return $fallbackWhenEmpty ? $default : '';
        }

        return mb_substr($value, 0, $limit);
    }

    private static function safeUrl(string $value): bool
    {
        if ($value === '' || ! filter_var($value, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true);
    }
}
