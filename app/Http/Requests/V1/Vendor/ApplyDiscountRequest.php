<?php

namespace App\Http\Requests\V1\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class ApplyDiscountRequest extends FormRequest
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
            'discount_type' => 'required|in:percent,amount',
            'discount_value' => 'required|numeric|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'discount_type.required' => 'Discount type is required.',
            'discount_type.in' => 'Discount type must be either percent or amount.',
            'discount_value.required' => 'Discount value is required.',
            'discount_value.numeric' => 'Discount value must be a valid number.',
            'discount_value.min' => 'Discount value cannot be negative.',
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

            // Validate amount discount doesn't exceed product price (if product has a price)
            if ($this->input('discount_type') === 'amount' && $this->route('product')) {
                $product = $this->route('product');
                if ($product->price && $this->input('discount_value') > $product->price) {
                    $validator->errors()->add('discount_value', 'Amount discount cannot exceed the product price.');
                }
            }
        });
    }
}