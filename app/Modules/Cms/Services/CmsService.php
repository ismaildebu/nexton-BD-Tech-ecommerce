<?php

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Models\CmsPage;

class CmsService
{
    public function create(array $data): CmsPage
    {
        return CmsPage::create($data);
    }
}
