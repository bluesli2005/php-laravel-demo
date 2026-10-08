<?php

namespace Database\Factories;

use App\Models\LifeInsurancePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LifeInsurancePolicy> */
class LifeInsurancePolicyFactory extends Factory
{
    protected $model = LifeInsurancePolicy::class;

    public function definition(): array
    {
        $effectiveDate = fake()->dateTimeBetween('-1 year', 'today');

        return [
            'policy_number' => 'POL-'.fake()->unique()->numerify('####'),
            'policyholder_name' => fake()->name(),
            'insured_name' => fake()->name(),
            'insured_birth_date' => fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d'),
            'beneficiary_name' => fake()->name(),
            'coverage_amount' => fake()->randomFloat(2, 10000, 1000000),
            'premium_amount' => fake()->randomFloat(2, 100, 100000),
            'currency' => 'CNY',
            'status' => 'draft',
            'effective_date' => $effectiveDate->format('Y-m-d'),
            'expiry_date' => (clone $effectiveDate)->modify('+1 year')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
