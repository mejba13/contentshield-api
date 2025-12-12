<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class DmcaSendRequest extends FormRequest
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
            'dmca_id' => [
                'required',
                'integer',
                'exists:dmca_requests,id',
            ],
            'recipient_email' => [
                'nullable',
                'email',
                'max:255',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'dmca_id.required' => 'DMCA request ID is required.',
            'dmca_id.exists' => 'DMCA request not found.',
            'recipient_email.email' => 'Invalid email address.',
        ];
    }
}
