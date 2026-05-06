<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta charset="UTF-8">
        <style>
            body {
                font-family: DejaVu Sans, sans-serif;
                color: #111827;
                font-size: 13px;
                line-height: 1.6;
            }

            .header {
                border-bottom: 2px solid #111827;
                padding-bottom: 12px;
                margin-bottom: 24px;
            }

            h1 {
                margin: 0;
                font-size: 24px;
                text-transform: uppercase;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 16px;
            }

            th,
            td {
                border: 1px solid #d1d5db;
                padding: 9px;
                text-align: left;
            }

            .signature {
                margin-top: 60px;
                width: 260px;
                border-top: 1px solid #111827;
                padding-top: 8px;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>{{ ucfirst($documentType) }}</h1>
            <p>{{ $serviceRequest->office->municipality->name ?? 'Municipality' }} - {{ $serviceRequest->office->name ?? 'Office' }}</p>
        </div>

        <p>This document was generated automatically by the municipal e-services platform.</p>

        <table>
            <tr>
                <th>Request Number</th>
                <td>{{ $serviceRequest->request_number }}</td>
            </tr>
            <tr>
                <th>Citizen</th>
                <td>{{ $serviceRequest->citizen->full_name ?? '' }}</td>
            </tr>
            <tr>
                <th>Service</th>
                <td>{{ $serviceRequest->service->name ?? '' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ str_replace('_', ' ', $serviceRequest->status) }}</td>
            </tr>
            <tr>
                <th>Generated At</th>
                <td>{{ now() }}</td>
            </tr>
            @if($documentType === 'receipt')
                <tr>
                    <th>Amount Paid</th>
                    <td>
                        @php($payment = $serviceRequest->payments->where('status', 'success')->first())
                        {{ $payment ? $payment->amount . ' ' . $payment->currency : 'No successful payment found' }}
                    </td>
                </tr>
            @endif
            <tr>
                <th>Tracking Link</th>
                <td>{{ $trackingUrl }}</td>
            </tr>
        </table>

        @if($documentType === 'certificate')
            <p>This certificate confirms that the above request was recorded and processed by the office according to the current request status.</p>
        @elseif($documentType === 'approval')
            <p>This approval document confirms that the office has reviewed the request. Final validity depends on the current status shown above.</p>
        @else
            <p>This receipt summarizes the payment information connected to the request.</p>
        @endif

        <div class="signature">
            Authorized Office Signature
        </div>
    </body>
</html>
