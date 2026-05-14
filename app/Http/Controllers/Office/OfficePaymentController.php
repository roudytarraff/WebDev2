<?php

namespace App\Http\Controllers\Office;

use App\Models\Payment;

class OfficePaymentController extends OfficeBaseController
{
    public function index()
    {
        $office = $this->currentOffice();
        $payments = Payment::with(['request.service', 'user'])
            ->whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->latest()
            ->get();

        $totalRevenue = $payments->where('status', 'success')->sum('amount');

        return view('office.payments.index', compact('office', 'payments', 'totalRevenue'));
    }

    public function show(string $id)
    {
        $office = $this->currentOffice();
        $payment = Payment::with(['request.service', 'request.citizen', 'transactions', 'user'])
            ->whereHas('request', fn ($query) => $query->where('office_id', $office->id))
            ->findOrFail($id);

        return view('office.payments.show', compact('payment'));
    }
}
