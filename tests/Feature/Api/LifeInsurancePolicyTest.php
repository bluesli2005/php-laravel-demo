<?php

namespace Tests\Feature\Api;

use App\Models\LifeInsurancePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LifeInsurancePolicyTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'policy_number' => 'POL-1001',
            'policyholder_name' => '张三',
            'insured_name' => '张三',
            'insured_birth_date' => '1990-01-01',
            'beneficiary_name' => '李四',
            'coverage_amount' => 500000,
            'premium_amount' => 12000,
            'currency' => 'CNY',
            'status' => 'draft',
            'effective_date' => '2026-01-01',
            'expiry_date' => '2046-01-01',
            'notes' => '测试保单',
        ], $overrides);
    }

    public function test_crud_flow_works(): void
    {
        $response = $this->postJson('/api/v1/life-insurance-policies', $this->validData());
        $response->assertCreated()->assertJsonPath('data.policy_number', 'POL-1001');
        $id = $response->json('data.id');

        $this->getJson('/api/v1/life-insurance-policies')->assertOk()
            ->assertJsonPath('data.0.policy_number', 'POL-1001');

        $this->getJson('/api/v1/life-insurance-policies/'.$id)->assertOk()
            ->assertJsonPath('data.policyholder_name', '张三');

        $this->patchJson('/api/v1/life-insurance-policies/'.$id, [
            'status' => 'active',
            'coverage_amount' => 600000,
        ])->assertOk()->assertJsonPath('data.status', 'active');

        $this->deleteJson('/api/v1/life-insurance-policies/'.$id)->assertOk()
            ->assertJsonPath('data.policy_number', 'POL-1001');

        $this->assertDatabaseMissing('life_insurance_policies', ['id' => $id]);
    }

    public function test_duplicate_policy_number_returns_422(): void
    {
        LifeInsurancePolicy::factory()->create(['policy_number' => 'POL-1001']);

        $this->postJson('/api/v1/life-insurance-policies', $this->validData())
            ->assertUnprocessable()->assertJsonValidationErrors('policy_number');
    }

    public function test_invalid_data_returns_422(): void
    {
        $this->postJson('/api/v1/life-insurance-policies', $this->validData([
            'insured_birth_date' => now()->addDay()->toDateString(),
            'coverage_amount' => 0,
            'expiry_date' => '2025-01-01',
            'notes' => str_repeat('a', 1001),
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['insured_birth_date', 'coverage_amount', 'expiry_date', 'notes']);
    }

    public function test_missing_policy_returns_json_404(): void
    {
        $this->getJson('/api/v1/life-insurance-policies/999999')
            ->assertNotFound()->assertHeader('Content-Type', 'application/json')
            ->assertJsonStructure(['message']);
    }

    public function test_seeding_is_repeatable(): void
    {
        $this->seed();
        $this->seed();

        $this->assertDatabaseCount('life_insurance_policies', 3);
        $this->assertDatabaseHas('life_insurance_policies', ['policy_number' => 'POL-0001']);
    }

    public function test_list_can_filter_by_search_and_status(): void
    {
        $this->seed();

        $this->getJson('/api/v1/life-insurance-policies?search=张&status=active')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.policy_number', 'POL-0001');

        $this->getJson('/api/v1/life-insurance-policies?status=cancelled')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_invalid_status_filter_returns_422(): void
    {
        $this->getJson('/api/v1/life-insurance-policies?status=unknown')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
}
