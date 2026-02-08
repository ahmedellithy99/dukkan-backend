<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdCarouselRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'carousel_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120', // 5MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.max' => 'The title cannot exceed 255 characters.',
            'carousel_image.required' => 'The carousel image is required.',
            'carousel_image.image' => 'The file must be an image.',
            'carousel_image.mimes' => 'The image must be a file of type: jpeg, jpg, png, webp.',
            'carousel_image.max' => 'The image size cannot exceed 5MB.',
        ];
    }
}
