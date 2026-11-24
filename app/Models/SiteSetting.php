<?php

namespace App\Models;

use App\Support\Appearance;
use App\Support\HomepageDesigns;
use App\Support\HomepageSections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    public const SINGLETON_ID = 1;

    private const CACHE_KEY = 'site_settings.current';

    protected $fillable = [
        'site_name',
        'logo_path',
        'contact_email',
        'contact_phone',
        'announcement',
        'footer_description',
        'copyright_text',
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
        'homepage_design',
        'homepage_sections',
    ];

    protected $casts = [
        'border_radius' => 'integer',
        'container_width' => 'integer',
        'homepage_sections' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn () => cache()->forget(self::CACHE_KEY));
        static::deleted(fn () => cache()->forget(self::CACHE_KEY));
    }

    public static function current(): self
    {
        return cache()->rememberForever(self::CACHE_KEY, fn () => static::query()->firstOrCreate(
            ['id' => self::SINGLETON_ID],
            [
                'site_name' => 'NEXTON',
                'announcement' => 'Free nationwide delivery on orders over ৳2,500.',
                'footer_description' => 'The premium multivendor commerce platform in Bangladesh. Discover verified merchants, curated electronics, gadgets, and design essentials.',
                'copyright_text' => 'Nexton BD Tech E-Commerce Ltd. All rights reserved.',
                ...Appearance::defaults(),
                'homepage_design' => 'signature',
            ],
        ));
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }

    public function getFontUrlAttribute(): ?string
    {
        return Appearance::fontUrl($this->font_family);
    }

    public function appearanceTokens(): array
    {
        return Appearance::tokens($this->only(Appearance::FIELDS));
    }

    public function homepageDesign(): string
    {
        return HomepageDesigns::sanitize($this->homepage_design);
    }

    public function homepageSections(): array
    {
        return HomepageSections::sanitize($this->homepage_sections ?? []);
    }
}
