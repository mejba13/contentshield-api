<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ContentRegisterRequest extends FormRequest
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
            'post_id' => [
                'required',
                'integer',
                'min:1',
            ],
            'post_url' => [
                'required',
                'url',
                'max:2048',
            ],
            'post_title' => [
                'required',
                'string',
                'max:500',
            ],
            'fingerprint' => [
                'required',
                'string',
                'max:128',
            ],
            'content_hash' => [
                'required',
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
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'post_id.required' => 'Post ID is required.',
            'post_id.integer' => 'Post ID must be an integer.',
            'post_url.required' => 'Post URL is required.',
            'post_url.url' => 'Post URL must be a valid URL.',
            'post_title.required' => 'Post title is required.',
            'fingerprint.required' => 'Content fingerprint is required.',
            'content_hash.required' => 'Content hash is required.',
            'content_hash.size' => 'Content hash must be a 64-character SHA256 hash.',
        ];
    }
}
