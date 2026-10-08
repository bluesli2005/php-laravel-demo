<?php

namespace App\Http\Requests;

use App\Models\LifeInsurancePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLifeInsurancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'policy_number' => ['bail', 'required', 'string', 'max:50', 'unique:life_insurance_policies,policy_number'],
            'policyholder_name' => ['required', 'string', 'max:100'],
            'insured_name' => ['required', 'string', 'max:100'],
            'insured_birth_date' => ['required', 'date', 'before_or_equal:today'],
            'beneficiary_name' => ['nullable', 'string', 'max:100'],
            'coverage_amount' => ['required', 'numeric', 'gt:0'],
            'premium_amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'status' => ['sometimes', 'required', 'string', Rule::in(LifeInsurancePolicy::STATUSES)],
            'effective_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
