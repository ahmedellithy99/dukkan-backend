<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class ReorderProductImagesRequest extends FormRequest
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
            'order' => ['required', 'array', 'min:1'],
            'order.*.id' => ['required', 'integer', 'exists:media,id'],
            'order.*.order' => ['required', 'integer', 'min:0'],
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
            'order.required' => 'Order data is required',
            'order.array' => 'Order data must be an array',
            'order.min' => 'At least one image order must be specified',
            'order.*.id.required' => 'Image ID is required',
            'order.*.id.integer' => 'Image ID must be an integer',
            'order.*.id.exists' => 'Image does not exist',
            'order.*.order.required' => 'Order value is required',
            'order.*.order.integer' => 'Order value must be an integer',
            'order.*.order.min' => 'Order value must be at least 0',
        ];
    }
}
