<?php

namespace App\Http\Requests\Api;

use App\Models\DmcaRequest;
use Illuminate\Foundation\Http\FormRequest;

class DmcaGenerateRequest extends FormRequest
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
            'content_id' => [
                'required',
                'integer',
                'exists:contents,id',
            ],
            'monitoring_result_id' => [
                'nullable',
                'integer',
                'exists:monitoring_results,id',
            ],
            'infringing_url' => [
                'required',
                'url',
                'max:2048',
            ],
            'recipient_type' => [
                'required',
                'in:' . implode(',', array_keys(DmcaRequest::RECIPIENT_TYPES)),
            ],
            'owner_info' => [
                'nullable',
                'array',
            ],
            'owner_info.name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'owner_info.email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'owner_info.address' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content_id.required' => 'Content ID is required.',
            'content_id.exists' => 'Content not found.',
            'infringing_url.required' => 'Infringing URL is required.',
            'infringing_url.url' => 'Infringing URL must be a valid URL.',
            'recipient_type.required' => 'Recipient type is required.',
            'recipient_type.in' => 'Invalid recipient type.',
        ];
    }
}
