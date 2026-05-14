<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;

class TrackingController extends Controller
{
    public function show(string $qrCode)
    {
        $serviceRequest = ServiceRequest::with(['office', 'service', 'statusHistory.changedBy'])
            ->where('qr_code', $qrCode)
            ->firstOrFail();

        return view('tracking.show', compact('serviceRequest'));
    }
}
