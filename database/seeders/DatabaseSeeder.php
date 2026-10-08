<?php

namespace Database\Seeders;

use App\Models\LifeInsurancePolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            [
                'policy_number' => 'POL-0001',
                'policyholder_name' => '张三',
                'insured_name' => '张三',
                'insured_birth_date' => '1990-05-20',
                'beneficiary_name' => '李四',
                'coverage_amount' => 500000,
                'premium_amount' => 12000,
                'currency' => 'CNY',
                'status' => 'active',
                'effective_date' => '2026-01-01',
                'expiry_date' => '2046-01-01',
                'notes' => '示例保单一',
            ],
            [
                'policy_number' => 'POL-0002',
                'policyholder_name' => '王五',
                'insured_name' => '王五',
                'insured_birth_date' => '1985-11-03',
                'beneficiary_name' => '赵六',
                'coverage_amount' => 300000,
                'premium_amount' => 8000,
                'currency' => 'CNY',
                'status' => 'draft',
                'effective_date' => '2026-11-01',
                'expiry_date' => '2046-11-01',
                'notes' => '示例保单二',
            ],
            [
                'policy_number' => 'POL-0003',
                'policyholder_name' => '陈七',
                'insured_name' => '陈七',
                'insured_birth_date' => '1978-02-14',
                'beneficiary_name' => null,
                'coverage_amount' => 800000,
                'premium_amount' => 20000,
                'currency' => 'CNY',
                'status' => 'active',
                'effective_date' => '2025-06-01',
                'expiry_date' => '2045-06-01',
                'notes' => '示例保单三',
            ],
        ] as $policy) {
            LifeInsurancePolicy::query()->firstOrCreate(
                ['policy_number' => $policy['policy_number']],
                $policy,
            );
        }
    }
}
