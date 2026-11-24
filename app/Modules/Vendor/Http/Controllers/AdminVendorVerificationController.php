<?php

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Services\VendorStatusService;
use DomainException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminVendorVerificationController extends Controller
{
    public function __construct(private readonly VendorStatusService $statuses) {}

    public function index(): View
    {
        return view('admin.vendors.index', [
            'vendors' => Vendor::query()->latest()->paginate(20),
        ]);
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load(['documents', 'statusHistories' => fn ($query) => $query->latest()]);

        return view('admin.vendors.show', ['vendor' => $vendor]);
    }

    public function approve(Request $request, Vendor $vendor): RedirectResponse
    {
        try {
            $this->statuses->approve($vendor, $request->user());
        } catch (DomainException $exception) {
            return redirect()->route('admin.vendors.show', $vendor)->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.vendors.index')->with('status', __('Vendor approved.'));
    }

    public function reject(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $this->statuses->reject($vendor, $request->user(), $data['reason']);
        } catch (DomainException $exception) {
            return redirect()->route('admin.vendors.show', $vendor)->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.vendors.index')->with('status', __('Vendor rejected and products deactivated.'));
    }

    public function suspend(Request $request, Vendor $vendor): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);

        try {
            $this->statuses->suspend($vendor, $request->user(), $data['reason']);
        } catch (DomainException $exception) {
            return redirect()->route('admin.vendors.show', $vendor)->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.vendors.index')->with('status', __('Vendor suspended and products deactivated.'));
    }

    public function previewDocument(Vendor $vendor, VendorDocument $document): StreamedResponse
    {
        $document = $vendor->documents()->whereKey($document->getKey())->firstOrFail();
        abort_unless(in_array($document->storage_disk, ['local'], true), 404);

        $disk = Storage::disk($document->storage_disk);
        abort_unless($disk->exists($document->storage_path), 404);
        abort_unless(in_array($document->mime_type, ['application/pdf', 'image/jpeg', 'image/png'], true), 415);

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($document->original_filename)) ?: 'document';

        return $disk->response($document->storage_path, $filename, [
            'Content-Type' => $document->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }
}
