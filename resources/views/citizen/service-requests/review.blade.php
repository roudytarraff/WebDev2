<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Request</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
</head>

<body>
@include('citizen.navbar')

<main class="main">
    <header class="topbar">
        <div>
            <h1>Review Request</h1>
            <p>Step 5 of 5</p>
        </div>
    </header>

    <div class="back-row">
        @if((float) $service->price > 0)
            <a href="{{ route('citizen.service-requests.payment') }}" class="button secondary">Back</a>
        @elseif($service->requires_appointment)
            <a href="{{ route('citizen.service-requests.appointment') }}" class="button secondary">Back</a>
        @else
            <a href="{{ route('citizen.service-requests.documents') }}" class="button secondary">Back</a>
        @endif

        <a href="{{ route('citizen.service-requests.cancel') }}" class="button secondary">Cancel</a>
    </div>

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <section class="grid two">
        <div class="panel">
            <h2>Service</h2>

            <p><strong>Service:</strong> {{ $service->name }}</p>
            <p><strong>Office:</strong> {{ $service->office->name }}</p>
            <p><strong>Category:</strong> {{ $service->category->name ?? 'No category' }}</p>
            <p><strong>Price:</strong> ${{ number_format((float) $service->price, 2) }}</p>
        </div>

        <div class="panel">
            <h2>Request Details</h2>

            <p>{{ $wizard['description'] }}</p>
        </div>
    </section>

    <section class="grid two">
        <div class="panel">
            <h2>Uploaded Documents</h2>

            <table>
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>File</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($service->documents as $document)
                        <tr>
                            <td>{{ $document->document_name }}</td>
                            <td>
                                @if(!empty($wizard['documents'][$document->id]))
                                    {{ $wizard['documents'][$document->id]['file_name'] }}
                                @else
                                    Not uploaded
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Appointment and Payment</h2>

            @if($service->requires_appointment)
                @if($slot)
                    <p><strong>Appointment:</strong> {{ $slot->slot_date }} from {{ substr($slot->start_time, 0, 5) }} to {{ substr($slot->end_time, 0, 5) }}</p>
                @else
                    <p><strong>Appointment:</strong> Not selected</p>
                @endif
            @else
                <p><strong>Appointment:</strong> Not required</p>
            @endif

            @if((float) $service->price > 0)
                <p><strong>Payment Method:</strong> {{ strtoupper($wizard['payment_method'] ?? 'Not selected') }}</p>
            @else
                <p><strong>Payment:</strong> Free service</p>
            @endif
        </div>
    </section>

    <div class="panel">
        <h2>Submit Request</h2>

        <p>Please review all information before submitting. After submission, the office will receive your request.</p>

        <form method="POST" action="{{ route('citizen.service-requests.submit') }}">
            @csrf

            <button type="submit" class="button">
                Submit Request
            </button>
        </form>
    </div>
</main>
</body>
</html>