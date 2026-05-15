<?php

namespace App\Services;

use App\Models\OfficeStaff;
use App\Models\ServiceRequest;

class RequestAssignmentService
{
    private const OPEN_STATUSES = [
        'pending',
        'approved',
        'in_progress',
    ];

    public function assign(ServiceRequest $serviceRequest): ?int
    {
        if ($serviceRequest->assigned_to_user_id) {
            return $serviceRequest->assigned_to_user_id;
        }

        $staffMember = OfficeStaff::query()
            ->select('office_staff.*')
            ->where('office_staff.office_id', $serviceRequest->office_id)
            ->where('office_staff.status', 'active')
            ->leftJoin('service_requests', function ($join) {
                $join->on('service_requests.assigned_to_user_id', '=', 'office_staff.user_id')
                    ->whereColumn('service_requests.office_id', 'office_staff.office_id')
                    ->whereIn('service_requests.status', self::OPEN_STATUSES);
            })
            ->selectRaw('COUNT(service_requests.id) as open_requests_count')
            ->groupBy(
                'office_staff.id',
                'office_staff.office_id',
                'office_staff.user_id',
                'office_staff.job_title',
                'office_staff.status',
                'office_staff.created_at',
                'office_staff.updated_at'
            )
            ->orderBy('open_requests_count')
            ->orderBy('office_staff.id')
            ->first();

        if (! $staffMember) {
            return null;
        }

        $serviceRequest->assigned_to_user_id = $staffMember->user_id;
        $serviceRequest->saveQuietly();

        return $staffMember->user_id;
    }
}
