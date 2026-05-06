<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Request Details</title>
        <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">
    </head>
    <body>
        @include('office.navbar')

        <main class="main">
            <header class="topbar">
                <div>
                    <h1>Request Details</h1>
                    <p>{{ Auth::user()->full_name ?? '' }}</p>
                </div>
            </header>
            <div class="back-row">
                <a href="{{ route('office.requests.index') }}" class="button secondary">Back</a>
            </div>

            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert error">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <section class="grid two">
                <div class="panel">
                    <h2>Request</h2>
                    <p><strong>Number:</strong> {{ $serviceRequest->request_number }}</p>
                    <p><strong>Citizen:</strong> {{ $serviceRequest->citizen->full_name ?? '' }}</p>
                    <p><strong>Service:</strong> {{ $serviceRequest->service->name ?? '' }}</p>
                    <p><strong>Assigned To:</strong> {{ $serviceRequest->assignedTo->full_name ?? 'Unassigned' }}</p>
                    <p><strong>Status:</strong> <span class="badge">{{ str_replace('_', ' ', $serviceRequest->status) }}</span></p>
                    <p>{{ $serviceRequest->description }}</p>
                    <div class="actions">
                        <a href="{{ route('office.requests.chat', $serviceRequest->id) }}" class="button">Chat With Citizen</a>
                    </div>
                </div>

                <div class="panel">
                    <h2>QR Tracking</h2>
                    <p class="muted">Citizen scans this code to track the request status.</p>
                    <div class="qr-box">
                        <img src="{{ $qrImageUrl }}" alt="Request tracking QR code">
                    </div>
                    <p><strong>Code:</strong> {{ $serviceRequest->qr_code }}</p>
                    <p><a href="{{ $trackingUrl }}" target="_blank">{{ $trackingUrl }}</a></p>
                </div>
            </section>

            <section class="grid two">
                <div class="panel">
                    <h2>Update Status</h2>
                    <form method="POST" action="{{ route('office.requests.update-status', $serviceRequest->id) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <label>Status</label>
                            <select name="status">
                                @foreach(['pending','in_progress','approved','rejected','completed'] as $status)
                                    <option value="{{ $status }}" @selected($serviceRequest->status === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Assign To</label>
                            <select name="assigned_to_user_id">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers as $staff)
                                    <option value="{{ $staff->user_id }}" @selected($serviceRequest->assigned_to_user_id == $staff->user_id)>{{ $staff->user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Note</label>
                            <textarea name="note"></textarea>
                        </div>

                        <div>
                            <button>Update Request</button>
                        </div>
                    </form>
                </div>

                <div class="panel">
                    <h2>Document Automation</h2>
                    <p class="muted">Generate official PDFs for this request.</p>

                    <div class="actions">
                        @foreach(['certificate', 'receipt', 'approval'] as $type)
                            <form method="POST" action="{{ route('office.requests.generated-documents.store', $serviceRequest->id) }}">
                                @csrf
                                <input type="hidden" name="document_type" value="{{ $type }}">
                                <button>{{ ucfirst($type) }}</button>
                            </form>
                        @endforeach
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Generated</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceRequest->generatedDocuments as $document)
                                <tr>
                                    <td>{{ ucfirst($document->document_type) }}</td>
                                    <td>{{ $document->generated_at }}</td>
                                    <td>
                                        <a href="{{ route('office.requests.generated-documents.download', [$serviceRequest->id, $document->id]) }}">Download</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">No generated PDFs yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid two">
                <div class="panel">
                    <h2>Uploaded Documents</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Uploaded By</th>
                                <th>Role</th>
                                <th>File</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($serviceRequest->documents as $document)
                                <tr>
                                    <td>{{ $document->requiredDocument->document_name ?? $document->file_name }}</td>
                                    <td>{{ $document->uploadedBy->full_name ?? '' }}</td>
                                    <td>{{ str_replace('_', ' ', $document->document_role) }}</td>
                                    <td>
                                        <a href="{{ route('office.requests.documents.download', [$serviceRequest->id, $document->id]) }}">Download</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">No documents uploaded.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <form method="POST" action="{{ route('office.requests.documents.store', $serviceRequest->id) }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <label>Document Type</label>
                            <select name="required_document_id">
                                @foreach($serviceRequest->service->documents as $document)
                                    <option value="{{ $document->id }}">{{ $document->document_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label>Upload File</label>
                            <input type="file" name="document">
                        </div>

                        <div>
                            <button>Upload Office Document</button>
                        </div>
                    </form>
                </div>

                <div class="panel">
                    <h2>Status History</h2>
                    @forelse($serviceRequest->statusHistory as $history)
                        <div class="message">
                            <strong>{{ str_replace('_', ' ', $history->old_status) }} to {{ str_replace('_', ' ', $history->new_status) }}</strong>
                            <br>
                            {{ $history->note }}
                            <br>
                            <small>{{ $history->changedBy->full_name ?? '' }} - {{ $history->changed_at }}</small>
                        </div>
                    @empty
                        <p>No status changes yet.</p>
                    @endforelse
                </div>
            </section>
        </main>
    </body>
</html>
