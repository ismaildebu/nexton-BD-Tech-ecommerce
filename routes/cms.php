<?php

use App\Modules\Cms\Http\Controllers\CmsPageController;
use Illuminate\Support\Facades\Route;

Route::get('/pages/{slug}', [CmsPageController::class, 'show'])->name('cms.show');
Route::middleware(['auth', 'admin'])->post('/admin/cms/pages', [CmsPageController::class, 'store'])->name('cms.store');
