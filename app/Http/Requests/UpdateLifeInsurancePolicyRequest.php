<?php

namespace App\Http\Requests;

use App\Models\LifeInsurancePolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLifeInsurancePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        /** @var LifeInsurancePolicy $policy */
        $policy = $this->route('life_insurance_policy');

        return [
            'policy_number' => ['sometimes', 'required', 'string', 'max:50', Rule::unique('life_insurance_policies', 'policy_number')->ignore($policy)],
            'policyholder_name' => ['sometimes', 'required', 'string', 'max:100'],
            'insured_name' => ['sometimes', 'required', 'string', 'max:100'],
            'insured_birth_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'beneficiary_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'coverage_amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'premium_amount' => ['sometimes', 'required', 'numeric', 'gt:0'],
            'currency' => ['sometimes', 'required', 'string', 'size:3'],
            'status' => ['sometimes', 'required', 'string', Rule::in(LifeInsurancePolicy::STATUSES)],
            'effective_date' => ['sometimes', 'required', 'date'],
            'expiry_date' => ['sometimes', 'nullable', 'date', 'after_or_equal:effective_date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }
}
