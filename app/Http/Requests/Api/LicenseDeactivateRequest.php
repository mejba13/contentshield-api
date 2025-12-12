<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LicenseDeactivateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'license_key' => [
                'required',
                'string',
                'regex:/^CSAI-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/i',
            ],
            'site_url' => [
                'required',
                'url',
                'max:2048',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'license_key.required' => 'License key is required.',
            'license_key.regex' => 'Invalid license key format.',
            'site_url.required' => 'Site URL is required.',
            'site_url.url' => 'Site URL must be a valid URL.',
        ];
    }
}
