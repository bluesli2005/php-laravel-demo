<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LifeInsurancePolicy extends Model
{
    use HasFactory;

    public const array STATUSES = ['draft', 'active', 'expired', 'cancelled'];

    protected $fillable = [
        'policy_number',
        'policyholder_name',
        'insured_name',
        'insured_birth_date',
        'beneficiary_name',
        'coverage_amount',
        'premium_amount',
        'currency',
        'status',
        'effective_date',
        'expiry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'insured_birth_date' => 'date:Y-m-d',
            'coverage_amount' => 'decimal:2',
            'premium_amount' => 'decimal:2',
            'effective_date' => 'date:Y-m-d',
            'expiry_date' => 'date:Y-m-d',
        ];
    }
}
