<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'shop_id' => 'required|exists:shops,id',
            'subcategory_id' => 'required|exists:subcategories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0|max:999999.99',
            'discount_type' => 'nullable|in:percent,amount',
            'discount_value' => 'required_with:discount_type|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'main_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:4048',
            'secondary_image' => 'required|image|mimes:jpeg,jpg,png,webp|max:4048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'shop_id.required' => 'Shop is required.',
            'shop_id.exists' => 'Selected shop does not exist.',
            'subcategory_id.required' => 'Subcategory is required.',
            'subcategory_id.exists' => 'Selected subcategory does not exist.',
            'name.required' => 'Product name is required.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'discount_type.in' => 'Discount type must be either percent or amount.',
            'discount_value.numeric' => 'Discount value must be a valid number.',
            'discount_value.min' => 'Discount value cannot be negative.',
            'discount_value.required_with' => 'Discount value is required when discount type is specified.',
            'stock_quantity.required' => 'Stock quantity is required.',
            'stock_quantity.integer' => 'Stock quantity must be a whole number.',
            'stock_quantity.min' => 'Stock quantity cannot be negative.',
            'images.required' => 'At least one image is required.',
            'images.array' => 'Images must be an array.',
            'images.*.image' => 'Each image must be a valid image file.',
            'images.*.mimes' => 'Image must be a file of type: jpeg, jpg, png, webp.',
            'images.*.max' => 'Image may not be greater than 4048 kilobytes.',
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

            // Validate amount discount doesn't exceed price
            if (
                $this->input('discount_type') === 'amount' &&
                $this->filled('price') &&
                $this->input('discount_value') > $this->input('price')
            ) {
                $validator->errors()->add('discount_value', 'Amount discount cannot exceed the product price.');
            }
        });
    }
}
