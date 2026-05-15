<?php

namespace App\Services;

use App\Models\OfficeStaff;
use App\Models\Service;
use App\Models\ServiceRequest;
use Illuminate\Support\Collection;

class RequestAssignmentService
{
    private const ACTIVE_STATUSES = ['pending', 'approved', 'in_progress'];

    public function assignToStaff(Service $service): ?int
    {
        $service->loadMissing('category');

        $activeStaff = OfficeStaff::with('user')
            ->where('office_id', $service->office_id)
            ->where('status', 'active')
            ->get();

        if ($activeStaff->isEmpty()) {
            return null;
        }

        $matchedStaff = $this->matchingStaff($activeStaff, $service);
        $candidateStaff = $activeStaff;

        if ($matchedStaff->isNotEmpty()) {
            $officeLowestLoad = $this->minimumLoad($activeStaff);
            $matchedLowestLoad = $this->minimumLoad($matchedStaff);

            if ($matchedLowestLoad <= $officeLowestLoad + 1) {
                $candidateStaff = $matchedStaff;
            }
        }

        return $this->leastBusyStaffUserId($candidateStaff);
    }

    private function matchingStaff(Collection $activeStaff, Service $service): Collection
    {
        $searchText = strtolower($service->name . ' ' . ($service->category->name ?? '') . ' ' . $service->description);
        $preferredTitles = $this->preferredTitles($searchText);

        if (empty($preferredTitles)) {
            return collect();
        }

        return $activeStaff->filter(function (OfficeStaff $staff) use ($preferredTitles) {
            $jobTitle = strtolower($staff->job_title ?? '');

            foreach ($preferredTitles as $preferredTitle) {
                if (str_contains($jobTitle, $preferredTitle)) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    private function preferredTitles(string $searchText): array
    {
        $rules = [
            ['keywords' => ['payment', 'tax', 'revenue', 'fee'], 'titles' => ['payment', 'tax']],
            ['keywords' => ['appointment', 'slot', 'schedule'], 'titles' => ['appointment']],
            ['keywords' => ['document', 'certificate', 'record', 'civil', 'residence', 'birth', 'marriage'], 'titles' => ['document', 'record', 'civil']],
            ['keywords' => ['permit', 'licens', 'building', 'renovation', 'sign', 'heritage'], 'titles' => ['permit', 'licens', 'inspection']],
            ['keywords' => ['health', 'food', 'safety', 'inspection'], 'titles' => ['health', 'inspection']],
            ['keywords' => ['complaint', 'support', 'citizen', 'noise'], 'titles' => ['support', 'service coordinator', 'front desk']],
            ['keywords' => ['environment', 'tree', 'sanitation'], 'titles' => ['operations', 'municipal agent', 'service coordinator']],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['keywords'] as $keyword) {
                if (str_contains($searchText, $keyword)) {
                    return $rule['titles'];
                }
            }
        }

        return [];
    }

    private function leastBusyStaffUserId(Collection $candidateStaff): ?int
    {
        $userIds = $candidateStaff->pluck('user_id')->filter()->values();

        if ($userIds->isEmpty()) {
            return null;
        }

        $loads = ServiceRequest::selectRaw('assigned_to_user_id, count(*) as request_count')
            ->whereIn('assigned_to_user_id', $userIds)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->groupBy('assigned_to_user_id')
            ->pluck('request_count', 'assigned_to_user_id');

        return $candidateStaff
            ->sortBy(fn (OfficeStaff $staff) => $loads[$staff->user_id] ?? 0)
            ->first()
            ?->user_id;
    }

    private function minimumLoad(Collection $candidateStaff): int
    {
        $userIds = $candidateStaff->pluck('user_id')->filter()->values();

        if ($userIds->isEmpty()) {
            return 0;
        }

        $loads = ServiceRequest::selectRaw('assigned_to_user_id, count(*) as request_count')
            ->whereIn('assigned_to_user_id', $userIds)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->groupBy('assigned_to_user_id')
            ->pluck('request_count', 'assigned_to_user_id');

        return (int) $candidateStaff
            ->map(fn (OfficeStaff $staff) => $loads[$staff->user_id] ?? 0)
            ->min();
    }
}
