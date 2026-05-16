<?php

namespace App\Http\Requests\Citizen;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCitizenProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'phone_number' => ['required', 'string', 'max:30'],

            'id_document' => [
                 'required',
                 'file',
                 'mimetypes:image/jpeg,image/png,image/jpg,image/webp,application/pdf',
                 'max:5120',
                 ],

            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:120'],
            'region' => ['required', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_number.required' => 'Please enter your phone number.',

            'id_document.required' => 'Please upload your national ID document.',
            'id_document.file' => 'The ID document must be a valid uploaded file.',
            'id_document.mimes' => 'The ID document must be a JPG, JPEG, PNG, or PDF file.',
            'id_document.max' => 'The ID document must not be larger than 5MB.',

            'address_line_1.required' => 'Please enter your address.',
            'city.required' => 'Please enter your city.',
            'region.required' => 'Please enter your region.',
            'country.required' => 'Please enter your country.',
        ];
    }
}