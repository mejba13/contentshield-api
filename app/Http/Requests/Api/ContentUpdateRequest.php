<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ContentUpdateRequest extends FormRequest
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
            'post_url' => [
                'nullable',
                'url',
                'max:2048',
            ],
            'post_title' => [
                'nullable',
                'string',
                'max:500',
            ],
            'fingerprint' => [
                'nullable',
                'string',
                'max:128',
            ],
            'content_hash' => [
                'nullable',
                'string',
                'size:64',
            ],
            'watermark_data' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'word_count' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'monitoring_enabled' => [
                'nullable',
                'boolean',
            ],
            'status' => [
                'nullable',
                'in:active,paused,deleted',
            ],
        ];
    }
}
