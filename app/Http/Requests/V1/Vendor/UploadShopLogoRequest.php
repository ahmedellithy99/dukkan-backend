<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UploadShopLogoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:3048', // 3MB max
                'dimensions:min_width=100,min_height=100,max_width=5000,max_height=6000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'logo.required' => 'Logo image is required',
            'logo.image' => 'The file must be a valid image',
            'logo.mimes' => 'Logo must be in JPEG, JPG, PNG, or WebP format',
            'logo.max' => 'Logo must not exceed 2MB',
            'logo.dimensions' => 'Logo dimensions must be between 100x100 and 2000x2000 pixels',
        ];
    }
}
