<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LicenseValidateRequest extends FormRequest
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
            'site_hash' => [
                'required',
                'string',
                'size:64',
            ],
            'plugin_version' => [
                'nullable',
                'string',
                'max:20',
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
            'license_key.regex' => 'Invalid license key format. Expected format: CSAI-XXXX-XXXX-XXXX-XXXX',
            'site_url.required' => 'Site URL is required.',
            'site_url.url' => 'Site URL must be a valid URL.',
            'site_hash.required' => 'Site hash is required.',
            'site_hash.size' => 'Site hash must be a 64-character SHA256 hash.',
        ];
    }
}
