<?php

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Models\CmsPage;
use DomainException;
use Illuminate\Support\Str;

class CmsService
{
    public function create(array $data): CmsPage
    {
        $data['slug'] = Str::slug((string) ($data['slug'] ?? ''));

        if ($data['slug'] === '') {
            throw new DomainException(__('A valid page slug is required.'));
        }

        return CmsPage::create($data);
    }
}
