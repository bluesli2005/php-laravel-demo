<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('life_insurance_policies', function (Blueprint $table): void {
            $table->id();
            $table->string('policy_number', 50)->unique();
            $table->string('policyholder_name', 100);
            $table->string('insured_name', 100);
            $table->date('insured_birth_date');
            $table->string('beneficiary_name', 100)->nullable();
            $table->decimal('coverage_amount', 15, 2);
            $table->decimal('premium_amount', 15, 2);
            $table->char('currency', 3)->default('CNY');
            $table->string('status', 20)->default('draft');
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('life_insurance_policies');
    }
};
