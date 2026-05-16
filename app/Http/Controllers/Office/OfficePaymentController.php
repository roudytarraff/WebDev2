<?php

namespace App\Http\Controllers\Office;

use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;

class OfficePaymentController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();

        $payments = Payment::with(['request.service', 'user', 'transactions'])
            ->whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->latest()
            ->get();

        $totalRevenue = $payments->where('status', 'success')->sum('amount');

        return view('office.payments.index', compact('office', 'payments', 'totalRevenue'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();

        $payment = Payment::with([
                'request.service',
                'request.citizen',
                'request.office.municipality',
                'request.office.address',
                'transactions',
                'user',
            ])
            ->whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->findOrFail($id);

        return view('office.payments.show', compact('payment'));
    }

    public function receipt(string $id)
    {
        $office = $this->currentOffice();

        $payment = Payment::with([
                'request.service',
                'request.citizen',
                'request.office.municipality',
                'request.office.address',
                'transactions',
                'user',
            ])
            ->whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->findOrFail($id);

        if ($payment->status !== 'success') {
            return back()->withErrors([
                'receipt' => 'A receipt can only be downloaded for successful payments.',
            ]);
        }

        $fileName = 'receipt-' . ($payment->request->request_number ?? $payment->id) . '.pdf';

        $pdf = Pdf::loadView('pdfs.payment_receipt', compact('payment'));

        return $pdf->download($fileName);
    }
}