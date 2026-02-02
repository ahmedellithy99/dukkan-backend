<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'whatsapp_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^(\+20|0)?1[0-9]{9}$/', // Egyptian mobile number format
            ],
            'phone_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^(\+20|0)?1[0-9]{9}$/', // Egyptian mobile number format
            ],
            'is_active' => 'sometimes|boolean',

            // location attributes
            'city_id' => 'required|numeric|exists:cities,id',
            'area' => 'required|string|max:255|min:3',
            'latitude' => 'required|numeric|between:22,32', // Egypt bounds
            'longitude' => 'required|numeric|between:25,37', // Egypt bounds

            // media attributes
            'logo' => 'required|image|mimes:jpeg,jpg,png,webp|max:4048', // 4MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'location_id.required' => 'Location is required.',
            'location_id.exists' => 'Selected location does not exist.',
            'location_id.unique' => 'This location already has a shop.',
            'name.required' => 'Shop name is required.',
            'name.max' => 'Shop name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'whatsapp_number.required' => 'WhatsApp number is required.',
            'whatsapp_number.regex' => 'WhatsApp number must be a valid Egyptian mobile number.',
            'phone_number.required' => 'Phone number is required.',
            'phone_number.regex' => 'Phone number must be a valid Egyptian mobile number.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be a JPEG, JPG, PNG, or WebP image.',
            'logo.max' => 'Logo file size cannot exceed 2MB.',
            'city_id.required' => 'City is required.',
            'city_id.exists' => 'Selected city does not exist.',
            'area.required' => 'Area is required.',
            'area.min' => 'Area must be at least 3 characters.',
            'area.max' => 'Area cannot exceed 255 characters.',
            'latitude.required' => 'Latitude is required.',
            'latitude.between' => 'Latitude must be within Egypt bounds (22-32).',
            'longitude.required' => 'Longitude is required.',
            'longitude.between' => 'Longitude must be within Egypt bounds (25-37).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set owner_id to authenticated user
        $this->merge([
            'owner_id' => $this->user()->id,
        ]);
    }

    /**
     * Get the validated data from the request.
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Add owner_id to validated data
        $validated['owner_id'] = $this->user()->id;

        return $validated;
    }
}
