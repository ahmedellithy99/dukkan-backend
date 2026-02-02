<?php

namespace App\Http\Requests\V1\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubcategoryRequest extends FormRequest
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
            'name' => 'required|unique:subcategories,name|string|max:40',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Subcategory name is required.',
            'name.max' => 'Subcategory name cannot exceed 40 characters.',
            'name.unique' => 'This subcategory name is already in use.'
        ];
    }
}