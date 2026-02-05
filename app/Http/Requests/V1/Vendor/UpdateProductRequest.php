<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'subcategory_id' => 'sometimes|exists:subcategories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'sometimes|numeric|min:0|max:999999.99',
            'discount_type' => 'nullable|in:percent,amount',
            'discount_value' => 'required_with:discount_type|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'main_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:4048',
            'secondary_image' => 'sometimes|image|mimes:jpeg,png,jpg,webp|max:4048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'subcategory_id.exists' => 'Selected subcategory does not exist.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'discount_type.in' => 'Discount type must be either percent or amount.',
            'discount_value.numeric' => 'Discount value must be a valid number.',
            'discount_value.min' => 'Discount value cannot be negative.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'stock_quantity.min' => 'Stock quantity cannot be negative.',
            'main_image.image' => 'Main image must be a valid image file (jpeg, png, jpg, webp).',
            'main_image.max' => 'Main image size cannot exceed 4MB.',
            'secondary_image.image' => 'Secondary image must be a valid image file (jpeg, png, jpg, webp).',
            'secondary_image.max' => 'Secondary image size cannot exceed 4MB.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate percent discount doesn't exceed 100%
            if ($this->input('discount_type') === 'percent' && $this->input('discount_value') > 100) {
                $validator->errors()->add('discount_value', 'Percent discount cannot exceed 100%.');
            }

            // Validate amount discount doesn't exceed price (if price is being updated)
            if ($this->input('discount_type') === 'amount' && 
                $this->filled('price') && 
                $this->input('discount_value') > $this->input('price')) {
                $validator->errors()->add('discount_value', 'Amount discount cannot exceed the product price.');
            }
        });
    }
}