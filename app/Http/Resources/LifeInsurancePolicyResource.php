<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LifeInsurancePolicyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'policy_number' => $this->policy_number,
            'policyholder_name' => $this->policyholder_name,
            'insured_name' => $this->insured_name,
            'insured_birth_date' => $this->insured_birth_date?->format('Y-m-d'),
            'beneficiary_name' => $this->beneficiary_name,
            'coverage_amount' => $this->coverage_amount,
            'premium_amount' => $this->premium_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'expiry_date' => $this->expiry_date?->format('Y-m-d'),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
