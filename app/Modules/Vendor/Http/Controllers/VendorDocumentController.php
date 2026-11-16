<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VendorDocumentController extends Controller
{
    public function index(Vendor $vendor): View
    {
        return view('vendor.documents.index', ['vendor' => $vendor, 'documents' => $vendor->documents]);
    }

    public function store(Request $request, Vendor $vendor): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'string', 'max:64'],
            'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store('vendor-documents', 'local');

        $vendor->documents()->create([
            'document_type' => $request->string('document_type')->toString(),
            'original_filename' => $file->getClientOriginalName(),
            'storage_disk' => 'local',
            'storage_path' => $path,
            'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('vendor.documents.index', $vendor)->with('status', __('Document uploaded.'));
    }
}
