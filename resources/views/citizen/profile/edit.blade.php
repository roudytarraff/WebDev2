<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Citizen Profile</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #0f172a;
        }

        .page-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            padding: 35px 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #0f172a;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #64748b;
            font-size: 16px;
        }

        .header-actions a {
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
            margin-left: 12px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left: 5px solid #dc2626;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .alert-error ul {
            margin-top: 8px;
            margin-left: 20px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 5px solid #16a34a;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .ocr-result {
            background: #eff6ff;
            color: #1e3a8a;
            border-left: 5px solid #2563eb;
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .ocr-result pre {
            background: #ffffff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 12px;
            margin-top: 10px;
            white-space: pre-wrap;
            word-break: break-word;
            color: #0f172a;
            font-size: 13px;
        }

        .form-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(15, 23, 42, 0.08);
            border: 1px solid #e2e8f0;
        }

        .form-section {
            margin-bottom: 32px;
            padding-bottom: 28px;
            border-bottom: 1px solid #e2e8f0;
        }

        .form-section:last-of-type {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .section-header {
            margin-bottom: 20px;
        }

        .section-header h2 {
            font-size: 23px;
            color: #0f172a;
            margin-bottom: 6px;
        }

        .section-header p {
            color: #64748b;
            font-size: 15px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .form-field {
            display: flex;
            flex-direction: column;
        }

        .form-field.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #1e293b;
            font-size: 15px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            background: #ffffff;
        }

        input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        input[type="file"] {
            padding: 10px;
            cursor: pointer;
            background: #f8fafc;
        }

        .field-error {
            color: #dc2626;
            font-size: 13px;
            margin-top: 6px;
        }

        .help-text {
            color: #64748b;
            font-size: 13px;
            margin-top: 7px;
            line-height: 1.5;
        }

        .api-note {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 15px;
            font-size: 14px;
            line-height: 1.6;
        }

        .warning-note {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            border-radius: 12px;
            padding: 14px 16px;
            margin-top: 15px;
            font-size: 14px;
            line-height: 1.6;
        }

        .submit-area {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn {
            border: none;
            border-radius: 10px;
            padding: 13px 22px;
            font-weight: bold;
            cursor: pointer;
            font-size: 15px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-secondary:hover {
            background: #cbd5e1;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            .header-actions a {
                margin-left: 0;
                margin-right: 12px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 22px;
            }

            .submit-area {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="page-wrapper">

        <header class="page-header">
            <div>
                <h1>Verify Citizen Profile</h1>
                <p>Upload your national ID to complete your citizen profile.</p>
            </div>

            <div class="header-actions">
                <a href="{{ route('home') }}">Home</a>
                <a href="{{ route('citizen.profile.show') }}">Back to Profile</a>
            </div>
        </header>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error">
                <strong>Please fix the following:</strong>

                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('ocr_raw_text') || session('ocr_detected_id') || session('ocr_detected_dob'))
            <div class="ocr-result">
                <strong>OCR Result</strong>

                <p style="margin-top: 8px;">
                    <strong>Detected National ID:</strong>
                    {{ session('ocr_detected_id') ?: 'Not detected' }}
                </p>

                <p>
                    <strong>Detected Date of Birth:</strong>
                    {{ session('ocr_detected_dob') ?: 'Not detected' }}
                </p>

                @if(session('ocr_raw_text'))
                    <p style="margin-top: 8px;">
                        <strong>Raw OCR Text:</strong>
                    </p>

                    <pre>{{ session('ocr_raw_text') }}</pre>
                @endif
            </div>
        @endif

        <form action="{{ route('citizen.profile.update') }}" method="POST" enctype="multipart/form-data" class="form-card">
            @csrf
            @method('PUT')

            <section class="form-section">
                <div class="section-header">
                    <h2>Contact Information</h2>
                    <p>Update your phone number so the office can contact you if needed.</p>
                </div>

                <div class="form-grid">
                    <div class="form-field full-width">
                        <label for="phone_number">Phone Number</label>

                        <input
                            id="phone_number"
                            type="text"
                            name="phone_number"
                            value="{{ old('phone_number', $user->phone_number ?? $user->phone ?? '') }}"
                            placeholder="Example: 70000003"
                            required
                        >

                        @error('phone_number')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-header">
                    <h2>National ID Verification</h2>
                    <p>Upload a clear national ID image or PDF. The system will read it using OCR.space.</p>
                </div>

                <div class="form-grid">
                    <div class="form-field full-width">
                        <label for="id_document">National ID Document</label>

                        <input
                            id="id_document"
                            type="file"
                            name="id_document"
                            accept=".jpg,.jpeg,.png,.pdf"
                            required
                        >

                        @error('id_document')
                            <div class="field-error">{{ $message }}</div>
                        @enderror

                        <span class="help-text">
                            Accepted files: JPG, JPEG, PNG, PDF. Maximum size: 5MB.
                            Make sure the national ID number at the bottom and the date of birth are clear and readable.
                        </span>

                        <div class="api-note">
                            After you submit, Laravel sends this file to OCR.space.
                            OCR.space extracts the text. If the system detects a valid 10-digit national ID and date of birth,
                            your citizen profile is verified automatically.
                        </div>

                        <div class="warning-note">
                            For best results, upload a straight, clear, well-lit image. Crop the image to the ID card only.
                            The bottom national ID number must be sharp and readable. If OCR cannot read the full 10-digit number,
                            verification will fail instead of saving an incorrect ID.
                        </div>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <div class="section-header">
                    <h2>Address Information</h2>
                    <p>Enter your current address. This information will be linked to your citizen profile.</p>
                </div>

                <div class="form-grid">
                    <div class="form-field">
                        <label for="address_line_1">Address Line 1</label>

                        <input
                            id="address_line_1"
                            type="text"
                            name="address_line_1"
                            value="{{ old('address_line_1', $profile->address->address_line_1 ?? '') }}"
                            placeholder="Street, building, floor"
                            required
                        >

                        @error('address_line_1')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="address_line_2">Address Line 2</label>

                        <input
                            id="address_line_2"
                            type="text"
                            name="address_line_2"
                            value="{{ old('address_line_2', $profile->address->address_line_2 ?? '') }}"
                            placeholder="Optional"
                        >

                        @error('address_line_2')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="city">City</label>

                        <input
                            id="city"
                            type="text"
                            name="city"
                            value="{{ old('city', $profile->address->city ?? '') }}"
                            placeholder="Example: Beirut"
                            required
                        >

                        @error('city')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="region">Region</label>

                        <input
                            id="region"
                            type="text"
                            name="region"
                            value="{{ old('region', $profile->address->region ?? '') }}"
                            placeholder="Example: Beirut"
                            required
                        >

                        @error('region')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="postal_code">Postal Code</label>

                        <input
                            id="postal_code"
                            type="text"
                            name="postal_code"
                            value="{{ old('postal_code', $profile->address->postal_code ?? '') }}"
                            placeholder="Optional"
                        >

                        @error('postal_code')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-field">
                        <label for="country">Country</label>

                        <input
                            id="country"
                            type="text"
                            name="country"
                            value="{{ old('country', $profile->address->country ?? 'Lebanon') }}"
                            placeholder="Example: Lebanon"
                            required
                        >

                        @error('country')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </section>

            <div class="submit-area">
                <a href="{{ route('citizen.profile.show') }}" class="btn btn-secondary">
                    Cancel
                </a>

                <button type="submit" class="btn btn-primary">
                    Upload ID & Verify Profile
                </button>
            </div>
        </form>

    </div>
</body>
</html>