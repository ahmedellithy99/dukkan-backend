<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdCarouselRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'link_url' => 'nullable|url|max:500',
            'display_order' => 'sometimes|integer|min:0',
            'carousel_image' => 'sometimes|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.max' => 'The title cannot exceed 255 characters.',
            'link_url.url' => 'The link URL must be a valid URL.',
            'link_url.max' => 'The link URL cannot exceed 500 characters.',
            'display_order.integer' => 'The display order must be an integer.',
            'display_order.min' => 'The display order must be at least 0.',
            'carousel_image.image' => 'The file must be an image.',
            'carousel_image.mimes' => 'The image must be a file of type: jpeg, jpg, png, webp.',
            'carousel_image.max' => 'The image size cannot exceed 5MB.',
        ];
    }
}
