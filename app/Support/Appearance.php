<?php

namespace App\Support;

class Appearance
{
    public const FIELDS = [
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
    ];

    private const PRESETS = [
        'signature' => [
            'label' => 'Signature orange',
            'values' => [
                'primary_color' => '#ff5b22',
                'accent_color' => '#7558e8',
                'neutral_color' => '#64748b',
                'font_family' => 'jakarta',
                'border_radius' => 12,
                'container_width' => 1280,
                'density' => 'comfortable',
                'header_style' => 'classic',
                'footer_style' => 'classic',
                'card_style' => 'soft',
            ],
        ],
        'emerald' => [
            'label' => 'Fresh emerald',
            'values' => [
                'primary_color' => '#059669',
                'accent_color' => '#0891b2',
                'neutral_color' => '#64748b',
                'font_family' => 'inter',
                'border_radius' => 10,
                'container_width' => 1240,
                'density' => 'comfortable',
                'header_style' => 'minimal',
                'footer_style' => 'classic',
                'card_style' => 'soft',
            ],
        ],
        'royal' => [
            'label' => 'Royal indigo',
            'values' => [
                'primary_color' => '#4f46e5',
                'accent_color' => '#d97706',
                'neutral_color' => '#64748b',
                'font_family' => 'manrope',
                'border_radius' => 16,
                'container_width' => 1320,
                'density' => 'spacious',
                'header_style' => 'centered',
                'footer_style' => 'dark',
                'card_style' => 'elevated',
            ],
        ],
        'berry' => [
            'label' => 'Modern berry',
            'values' => [
                'primary_color' => '#db2777',
                'accent_color' => '#7c3aed',
                'neutral_color' => '#71717a',
                'font_family' => 'poppins',
                'border_radius' => 20,
                'container_width' => 1200,
                'density' => 'compact',
                'header_style' => 'classic',
                'footer_style' => 'minimal',
                'card_style' => 'minimal',
            ],
        ],
        'mono' => [
            'label' => 'Monochrome',
            'values' => [
                'primary_color' => '#18181b',
                'accent_color' => '#52525b',
                'neutral_color' => '#71717a',
                'font_family' => 'system',
                'border_radius' => 4,
                'container_width' => 1440,
                'density' => 'comfortable',
                'header_style' => 'minimal',
                'footer_style' => 'dark',
                'card_style' => 'minimal',
            ],
        ],
        'custom' => [
            'label' => 'Custom',
            'values' => [],
        ],
    ];

    private const FONTS = [
        'jakarta' => [
            'label' => 'Plus Jakarta Sans',
            'stack' => "'Plus Jakarta Sans', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap',
        ],
        'inter' => [
            'label' => 'Inter',
            'stack' => "'Inter', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap',
        ],
        'manrope' => [
            'label' => 'Manrope',
            'stack' => "'Manrope', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap',
        ],
        'poppins' => [
            'label' => 'Poppins',
            'stack' => "'Poppins', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&display=swap',
        ],
        'noto' => [
            'label' => 'Noto Sans',
            'stack' => "'Noto Sans', 'Noto Sans Bengali', ui-sans-serif, system-ui, sans-serif",
            'url' => 'https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Noto+Sans:wght@400;500;600;700;800&display=swap',
        ],
        'system' => [
            'label' => 'System default',
            'stack' => 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif',
            'url' => null,
        ],
    ];

    private const DENSITIES = [
        'compact' => ['label' => 'Compact', 'section' => '1.75rem', 'control' => '2.5rem', 'header' => '4.5rem', 'font' => '0.9375rem'],
        'comfortable' => ['label' => 'Comfortable', 'section' => '2.25rem', 'control' => '2.75rem', 'header' => '5rem', 'font' => '1rem'],
        'spacious' => ['label' => 'Spacious', 'section' => '3rem', 'control' => '3rem', 'header' => '5.5rem', 'font' => '1.0625rem'],
    ];

    private const HEADER_STYLES = [
        'classic' => 'Classic',
        'centered' => 'Centered',
        'minimal' => 'Minimal',
    ];

    private const FOOTER_STYLES = [
        'classic' => 'Classic',
        'minimal' => 'Minimal',
        'dark' => 'Contrast dark',
    ];

    private const CARD_STYLES = [
        'soft' => 'Soft shadow',
        'elevated' => 'Elevated',
        'minimal' => 'Flat minimal',
    ];

    public static function defaults(): array
    {
        return ['appearance_preset' => 'signature', ...self::PRESETS['signature']['values']];
    }

    public static function presets(): array
    {
        return self::PRESETS;
    }

    public static function fonts(): array
    {
        return self::FONTS;
    }

    public static function options(): array
    {
        return [
            'presets' => array_map(fn (array $preset) => $preset['label'], self::PRESETS),
            'fonts' => array_map(fn (array $font) => $font['label'], self::FONTS),
            'densities' => array_map(fn (array $density) => $density['label'], self::DENSITIES),
            'header_styles' => self::HEADER_STYLES,
            'footer_styles' => self::FOOTER_STYLES,
            'card_styles' => self::CARD_STYLES,
        ];
    }

    public static function previewConfig(array $values): array
    {
        return [
            ...self::sanitize($values),
            'presets' => self::PRESETS,
            'fonts' => array_map(fn (array $font) => $font['stack'], self::FONTS),
        ];
    }

    public static function fontUrl(mixed $font): ?string
    {
        return is_string($font) && isset(self::FONTS[$font]) ? self::FONTS[$font]['url'] : null;
    }

    public static function sanitize(array $values): array
    {
        $defaults = self::defaults();
        $result = [];

        foreach (self::FIELDS as $field) {
            $result[$field] = $values[$field] ?? $defaults[$field];
        }

        foreach (['primary_color', 'accent_color', 'neutral_color'] as $field) {
            if (! is_string($result[$field]) || ! preg_match('/^#[0-9a-fA-F]{6}$/', $result[$field])) {
                $result[$field] = $defaults[$field];
            }

            $result[$field] = strtolower($result[$field]);
        }

        foreach (['appearance_preset' => self::PRESETS, 'font_family' => self::FONTS, 'density' => self::DENSITIES, 'header_style' => self::HEADER_STYLES, 'footer_style' => self::FOOTER_STYLES, 'card_style' => self::CARD_STYLES] as $field => $allowed) {
            if (! is_string($result[$field]) || ! array_key_exists($result[$field], $allowed)) {
                $result[$field] = $defaults[$field];
            }
        }

        $radius = filter_var($result['border_radius'], FILTER_VALIDATE_INT);
        $width = filter_var($result['container_width'], FILTER_VALIDATE_INT);
        $result['border_radius'] = max(0, min(24, $radius === false ? $defaults['border_radius'] : $radius));
        $result['container_width'] = max(1120, min(1536, $width === false ? $defaults['container_width'] : $width));

        return $result;
    }

    public static function tokens(array $values): array
    {
        $values = self::sanitize($values);
        $brand = self::palette($values['primary_color']);
        $accent = self::palette($values['accent_color']);
        $neutral = self::palette($values['neutral_color'], false, true);
        $density = self::DENSITIES[$values['density']];
        $radius = $values['border_radius'];
        $width = $values['container_width'];
        $font = self::FONTS[$values['font_family']];
        $cardShadow = match ($values['card_style']) {
            'elevated' => '0 18px 45px -20px rgb(15 23 42 / 0.28)',
            'minimal' => 'none',
            default => '0 1px 3px 0 rgb(15 23 42 / 0.06)',
        };
        $cardBorder = match ($values['card_style']) {
            'elevated' => '65%',
            'minimal' => '100%',
            default => '80%',
        };

        $tokens = [
            '--font-sans' => $font['stack'],
            '--font-size-base' => $density['font'],
            '--space-section' => $density['section'],
            '--control-height' => $density['control'],
            '--header-height' => $density['header'],
            '--container-width' => $width.'px',
            '--container-narrow' => max(960, $width - 256).'px',
            '--container-wide' => ($width + 160).'px',
            '--radius-sm' => max(0, $radius - 4).'px',
            '--radius-md' => $radius.'px',
            '--radius-lg' => ($radius + 2).'px',
            '--radius-xl' => ($radius + 4).'px',
            '--radius-2xl' => ($radius + 8).'px',
            '--radius-3xl' => ($radius + 16).'px',
            '--radius-control' => $radius.'px',
            '--radius-card' => ($radius + 8).'px',
            '--card-shadow' => $cardShadow,
            '--card-border-strength' => $cardBorder,
            '--surface' => $neutral[50],
            '--surface-strong' => '#ffffff',
            '--text' => $neutral[900],
            '--text-muted' => $neutral[500],
            '--border' => $neutral[200],
            '--header-bg' => 'rgb(255 255 255 / 0.96)',
            '--header-border' => $neutral[100],
            '--announcement-bg' => $neutral[900],
            '--footer-bg' => $values['footer_style'] === 'dark' ? $neutral[900] : '#ffffff',
            '--footer-text' => $values['footer_style'] === 'dark' ? $neutral[100] : $neutral[500],
            '--footer-heading' => $values['footer_style'] === 'dark' ? '#ffffff' : $neutral[900],
            '--footer-border' => $values['footer_style'] === 'dark' ? $neutral[800] : $neutral[100],
        ];

        foreach (['brand' => $brand, 'accent' => $accent, 'ink' => $neutral, 'slate' => $neutral] as $name => $palette) {
            foreach ($palette as $shade => $color) {
                $tokens["--color-{$name}-{$shade}"] = $color;
            }
        }

        return $tokens;
    }

    private static function palette(string $hex, bool $ensureContrast = true, bool $neutral = false): array
    {
        [$red, $green, $blue] = self::rgb($hex);

        if ($ensureContrast) {
            $attempts = 0;

            while (self::contrast($red, $green, $blue) < 3 && $attempts < 40) {
                $red = (int) floor($red * 0.95);
                $green = (int) floor($green * 0.95);
                $blue = (int) floor($blue * 0.95);
                $attempts++;
            }
        }

        $mix = $neutral
            ? [50 => [255, 255, 255, 0.95], 100 => [255, 255, 255, 0.88], 200 => [255, 255, 255, 0.75], 300 => [255, 255, 255, 0.6], 400 => [255, 255, 255, 0.42], 600 => [0, 0, 0, 0.12], 700 => [0, 0, 0, 0.25], 800 => [0, 0, 0, 0.4], 900 => [0, 0, 0, 0.58], 950 => [0, 0, 0, 0.72]]
            : [50 => [255, 255, 255, 0.92], 100 => [255, 255, 255, 0.84], 200 => [255, 255, 255, 0.7], 300 => [255, 255, 255, 0.54], 400 => [255, 255, 255, 0.3], 600 => [0, 0, 0, 0.12], 700 => [0, 0, 0, 0.26], 800 => [0, 0, 0, 0.4], 900 => [0, 0, 0, 0.55], 950 => [0, 0, 0, 0.65]];

        $palette = [500 => self::hex($red, $green, $blue)];

        foreach ($mix as $shade => [$targetRed, $targetGreen, $targetBlue, $amount]) {
            $palette[$shade] = self::hex(
                (int) round($red * (1 - $amount) + $targetRed * $amount),
                (int) round($green * (1 - $amount) + $targetGreen * $amount),
                (int) round($blue * (1 - $amount) + $targetBlue * $amount),
            );
        }

        ksort($palette);

        return $palette;
    }

    private static function rgb(string $hex): array
    {
        return [hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2))];
    }

    private static function hex(int $red, int $green, int $blue): string
    {
        return sprintf('#%02x%02x%02x', max(0, min(255, $red)), max(0, min(255, $green)), max(0, min(255, $blue)));
    }

    private static function contrast(int $red, int $green, int $blue): float
    {
        $luminance = self::luminance($red, $green, $blue);

        return 1.05 / ($luminance + 0.05);
    }

    private static function luminance(int $red, int $green, int $blue): float
    {
        $channels = array_map(static fn (int $channel): float => ($channel / 255) <= 0.04045
            ? ($channel / 255) / 12.92
            : ((($channel / 255) + 0.055) / 1.055) ** 2.4, [$red, $green, $blue]);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }
}
