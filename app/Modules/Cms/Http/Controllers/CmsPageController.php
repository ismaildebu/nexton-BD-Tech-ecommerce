<?php

namespace App\Modules\Cms\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Http\Requests\CreateCmsPageRequest;
use App\Modules\Cms\Models\CmsPage;
use App\Modules\Cms\Services\CmsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CmsPageController extends Controller
{
    public function __construct(private readonly CmsService $cms) {}

    public function show(string $slug): View
    {
        $page = CmsPage::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('cms.show', ['page' => $page]);
    }

    public function store(CreateCmsPageRequest $request): RedirectResponse
    {
        $this->cms->create($request->validated());

        return redirect()->route('home')->with('status', __('Page created.'));
    }
}
