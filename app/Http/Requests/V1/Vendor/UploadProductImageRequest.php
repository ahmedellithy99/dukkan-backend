<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UploadProductImageRequest extends FormRequest
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
            'images' => ['required', 'array', 'min:1', 'max:10'],
            'images.*' => [
                'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120', // 5MB max per image
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
            'images.required' => 'At least one image is required',
            'images.array' => 'Images must be provided as an array',
            'images.min' => 'At least one image is required',
            'images.max' => 'You can upload a maximum of 10 images at once',
            'images.*.required' => 'Each image file is required',
            'images.*.image' => 'Each file must be a valid image',
            'images.*.mimes' => 'Images must be in JPEG, JPG, PNG, or WebP format',
            'images.*.max' => 'Each image must not exceed 5MB',
        ];
    }
}
