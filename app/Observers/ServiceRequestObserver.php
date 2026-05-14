<?php

namespace App\Observers;

use App\Models\ServiceRequest;
use App\Services\RequestAssignmentService;

class ServiceRequestObserver
{
    public function creating(ServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->assigned_to_user_id) {
            return;
        }

        $serviceRequest->loadMissing('service.category');

        if (! $serviceRequest->service) {
            return;
        }

        $serviceRequest->assigned_to_user_id = app(RequestAssignmentService::class)->assignToStaff($serviceRequest->service);
    }
}
