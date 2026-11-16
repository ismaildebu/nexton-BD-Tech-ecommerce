<?php

namespace App\Modules\Identity\Enums;

enum AddressType: string
{
    case Home = 'home';
    case Office = 'office';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Home => __('Home'),
            self::Office => __('Office'),
            self::Other => __('Other'),
        };
    }
}
