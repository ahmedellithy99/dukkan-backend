<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'city_id' => 'sometimes|required|exists:cities,id',
            'area' => 'nullable|string|max:100',
            'latitude' => 'sometimes|required|numeric|between:22,32', // Egypt bounds
            'longitude' => 'sometimes|required|numeric|between:25,37', // Egypt bounds
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'city_id.required' => 'City is required.',
            'city_id.exists' => 'Selected city does not exist.',
            'area.max' => 'Area name cannot exceed 100 characters.',
            'latitude.required' => 'Latitude is required.',
            'latitude.numeric' => 'Latitude must be a valid number.',
            'latitude.between' => 'Latitude must be within Egypt bounds (22-32).',
            'longitude.required' => 'Longitude is required.',
            'longitude.numeric' => 'Longitude must be a valid number.',
            'longitude.between' => 'Longitude must be within Egypt bounds (25-37).',
        ];
    }
}