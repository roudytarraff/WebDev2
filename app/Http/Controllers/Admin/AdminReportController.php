<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Payment;
use App\Models\ServiceRequest;

class AdminReportController extends Controller
{
    public function index()
    {
        $offices = Office::with('municipality')
            ->withCount('serviceRequests')
            ->orderBy('name')
            ->get();

        $revenueByOffice = Office::with('municipality')
            ->get()
            ->map(function (Office $office) {
                $office->revenue = Payment::whereHas('request', function ($query) use ($office) {
                    $query->where('office_id', $office->id);
                })->where('status', 'success')->sum('amount');

                return $office;
            });

        $requestsByStatus = ServiceRequest::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get();

        $totalRevenue = Payment::where('status', 'success')->sum('amount');
        $pendingRevenue = Payment::where('status', 'pending')->sum('amount');

        return view('admin.reports.index', compact(
            'offices',
            'revenueByOffice',
            'requestsByStatus',
            'totalRevenue',
            'pendingRevenue'
        ));
    }
}
