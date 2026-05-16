<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
    <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}">

    <style>
        .documents-page {
            max-width: 1180px;
        }

        .request-actions {
            display: flex;
            gap: 12px;
            margin: 18px 0 22px;
            flex-wrap: wrap;
        }

        .documents-panel {
            max-width: 100%;
        }

        .documents-header {
            margin-bottom: 20px;
        }

        .documents-header h2 {
            margin-bottom: 8px;
        }

        .documents-header p {
            margin: 0;
            line-height: 1.5;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-top: 18px;
        }

        .document-card {
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            border-radius: 10px;
            padding: 16px;
            min-width: 0;
        }

        .document-card label {
            display: block;
            font-weight: 700;
            margin-bottom: 10px;
            color: #0f172a;
        }

        .required-star {
            color: #dc2626;
        }

        .document-card input[type="file"] {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            font-size: 14px;
        }

        .uploaded-file {
            margin-top: 8px;
            font-size: 14px;
            color: #64748b;
            word-break: break-word;
        }

        .documents-submit-row {
            display: flex;
            justify-content: flex-start;
            margin-top: 22px;
        }

        .documents-submit-row button {
            width: auto;
            min-width: 180px;
            padding-left: 24px;
            padding-right: 24px;
        }

        @media (max-width: 900px) {
            .documents-grid {
                grid-template-columns: 1fr;
            }

            .documents-submit-row button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
@include('citizen.navbar')

<main class="main documents-page">
    <header class="topbar">
        <div>
            <h1>Upload Documents</h1>
            <p>Step 2 of 5</p>
        </div>
    </header>

    <div class="request-actions">
        <a href="{{ route('citizen.service-requests.start', $service->id) }}" class="button secondary">
            Back
        </a>

        <a href="{{ route('citizen.service-requests.cancel') }}" class="button secondary">
            Cancel
        </a>
    </div>

    @if($errors->any())
        <div class="alert error">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="panel documents-panel">
        <div class="documents-header">
            <div class="panel-head">
                <h2>Required Documents</h2>
                <span class="badge">{{ $service->documents->count() }} Documents</span>
            </div>

            <p>
                Please upload the documents required for {{ $service->name }}.
            </p>

            <p class="muted">
                Accepted file types: PDF, JPG, JPEG, PNG. Maximum file size: 5 MB.
            </p>
        </div>

        <form method="POST" action="{{ route('citizen.service-requests.documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="documents-grid">
                @forelse($service->documents as $document)
                    <div class="document-card">
                        <label for="document_{{ $document->id }}">
                            {{ $document->document_name }}

                            @if($document->is_required)
                                <span class="required-star">*</span>
                            @else
                                <span class="muted">(Optional)</span>
                            @endif
                        </label>

                        <input
                            id="document_{{ $document->id }}"
                            type="file"
                            name="documents[{{ $document->id }}]"
                            accept=".pdf,.jpg,.jpeg,.png"
                            {{ $document->is_required && empty($wizard['documents'][$document->id]) ? 'required' : '' }}
                        >

                        @if(!empty($wizard['documents'][$document->id]))
                            <div class="uploaded-file">
                                Uploaded file: {{ $wizard['documents'][$document->id]['file_name'] }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="document-card">
                        <p>No documents are required for this service.</p>
                    </div>
                @endforelse
            </div>

            <div class="documents-submit-row">
                <button type="submit">
                    Continue
                </button>
            </div>
        </form>
    </div>
</main>
</body>
</html>