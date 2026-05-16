<?php

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitizenPaymentController extends Controller
{
    public function index(Request $request)
    {
        $statuses = ['pending', 'success', 'failed'];
        $methods = ['card', 'cash', 'crypto'];

        $statusFilter = $request->query('status');
        $methodFilter = $request->query('method');
        $search = trim((string) $request->query('search'));

        $baseQuery = Payment::where('user_id', Auth::id());

        $totalPayments = (clone $baseQuery)->count();
        $successfulPayments = (clone $baseQuery)->where('status', 'success')->count();
        $pendingPayments = (clone $baseQuery)->where('status', 'pending')->count();
        $totalPaid = (clone $baseQuery)->where('status', 'success')->sum('amount');

        $payments = Payment::with([
                'request.service',
                'request.office.municipality',
                'transactions',
            ])
            ->where('user_id', Auth::id())
            ->when($statusFilter, function ($query) use ($statusFilter, $statuses) {
                if (in_array($statusFilter, $statuses, true)) {
                    $query->where('status', $statusFilter);
                }
            })
            ->when($methodFilter, function ($query) use ($methodFilter, $methods) {
                if (in_array($methodFilter, $methods, true)) {
                    $query->where('payment_method', $methodFilter);
                }
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('transaction_reference', 'like', "%{$search}%")
                        ->orWhereHas('request', function ($requestQuery) use ($search) {
                            $requestQuery->where('request_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('request.service', function ($serviceQuery) use ($search) {
                            $serviceQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('request.office', function ($officeQuery) use ($search) {
                            $officeQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->get();

        return view('citizen.payments.index', compact(
            'payments',
            'statuses',
            'methods',
            'statusFilter',
            'methodFilter',
            'search',
            'totalPayments',
            'successfulPayments',
            'pendingPayments',
            'totalPaid'
        ));
    }

    public function show(Payment $payment)
    {
        $payment = Payment::with([
                'request.citizen',
                'request.service',
                'request.office.municipality',
                'request.office.address',
                'transactions',
                'user',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($payment->id);

        return view('citizen.payments.show', compact('payment'));
    }

    public function downloadReceipt(Payment $payment)
    {
        $payment = Payment::with([
                'request.citizen',
                'request.service',
                'request.office.municipality',
                'request.office.address',
                'transactions',
                'user',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($payment->id);

        if ($payment->status !== 'success') {
            return back()->withErrors([
                'receipt' => 'A receipt can only be downloaded after the payment is successful.',
            ]);
        }

        $fileName = 'receipt-' . ($payment->request->request_number ?? $payment->id) . '.pdf';

        $pdf = Pdf::loadView('pdfs.payment_receipt', compact('payment'));

        return $pdf->download($fileName);
    }
}