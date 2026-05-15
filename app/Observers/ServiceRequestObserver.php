<?php

namespace App\Observers;

use App\Models\ServiceRequest;
use App\Services\RequestAssignmentService;

class ServiceRequestObserver
{
    public function created(ServiceRequest $serviceRequest): void
    {
        app(RequestAssignmentService::class)->assign($serviceRequest);
    }
}
