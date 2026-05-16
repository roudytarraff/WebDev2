<?php

namespace App\Services;

use App\Models\ServiceRequest;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Storage;

class RequestQrCodeService
{
    public function generate(ServiceRequest $serviceRequest): string
    {
        if (! $serviceRequest->qr_code) {
            $serviceRequest->qr_code = $this->generateQrCodeValue($serviceRequest);
            $serviceRequest->save();
        }

        $trackingUrl = route('tracking.show', $serviceRequest->qr_code);

        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $qrSvg = $writer->writeString($trackingUrl);

        $filePath = $this->getPublicPath($serviceRequest);

        Storage::disk('public')->put($filePath, $qrSvg);

        return $filePath;
    }

    public function getPublicPath(ServiceRequest $serviceRequest): string
    {
        return 'request-qrcodes/request-' . $serviceRequest->id . '.svg';
    }

    public function exists(ServiceRequest $serviceRequest): bool
    {
        return Storage::disk('public')->exists($this->getPublicPath($serviceRequest));
    }

    private function generateQrCodeValue(ServiceRequest $serviceRequest): string
    {
        return 'QR-' . $serviceRequest->request_number . '-' . strtoupper(substr(md5($serviceRequest->id . now()), 0, 8));
    }
}