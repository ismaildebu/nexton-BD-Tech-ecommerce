<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Cms\Models\CmsPage;
use Illuminate\Contracts\View\View;

class CmsController extends Controller
{
    public function index(): View
    {
        return view('admin.cms.index', ['pages' => CmsPage::latest()->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.cms.create');
    }
}
