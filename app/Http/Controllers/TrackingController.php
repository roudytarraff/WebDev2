<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\RequestQrCodeService;
use Illuminate\Support\Facades\Storage;

class TrackingController extends Controller
{
    public function show(string $qrCode)
    {
        $serviceRequest = ServiceRequest::with([
            'office.municipality',
            'service.category',
            'statusHistory.changedBy',
            'payments',
            'appointments.slot',
        ])
            ->where('qr_code', $qrCode)
            ->firstOrFail();

        return view('tracking.show', compact('serviceRequest'));
    }

    public function qrImage(string $qrCode)
    {
        $serviceRequest = ServiceRequest::where('qr_code', $qrCode)
            ->firstOrFail();

        $qrService = app(RequestQrCodeService::class);

        if (! $qrService->exists($serviceRequest)) {
            $qrService->generate($serviceRequest);
        }

        $path = $qrService->getPublicPath($serviceRequest);

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response(Storage::disk('public')->get($path), 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}