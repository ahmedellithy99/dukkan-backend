<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeValueRequest extends FormRequest
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
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:100',
                Rule::unique('attribute_values')->where(function ($query) {
                    return $query->where('attribute_id', $this->input('attribute_id'));
                }),
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'attribute_id.required' => 'Attribute is required.',
            'attribute_id.exists' => 'Selected attribute does not exist.',
            'value.required' => 'Attribute value is required.',
            'value.max' => 'Attribute value cannot exceed 100 characters.',
            'value.unique' => 'This value already exists for the selected attribute.',
        ];
    }
}