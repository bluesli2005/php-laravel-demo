<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexLifeInsurancePolicyRequest;
use App\Http\Requests\StoreLifeInsurancePolicyRequest;
use App\Http\Requests\UpdateLifeInsurancePolicyRequest;
use App\Http\Resources\LifeInsurancePolicyResource;
use App\Models\LifeInsurancePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LifeInsurancePolicyController extends Controller
{
    public function index(IndexLifeInsurancePolicyRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();
        $search = trim($validated['search'] ?? '');

        $query = LifeInsurancePolicy::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('policy_number', 'like', "%{$search}%")
                        ->orWhere('policyholder_name', 'like', "%{$search}%")
                        ->orWhere('insured_name', 'like', "%{$search}%")
                        ->orWhere('beneficiary_name', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when(isset($validated['status']) && $validated['status'] !== '', function (Builder $query) use ($validated): void {
                $query->where('status', $validated['status']);
            });

        return LifeInsurancePolicyResource::collection(
            $query->latest()->get()
        );
    }

    public function store(StoreLifeInsurancePolicyRequest $request): LifeInsurancePolicyResource
    {
        return new LifeInsurancePolicyResource(
            LifeInsurancePolicy::query()->create($request->validated())
        );
    }

    public function show(LifeInsurancePolicy $lifeInsurancePolicy): LifeInsurancePolicyResource
    {
        return new LifeInsurancePolicyResource($lifeInsurancePolicy);
    }

    public function update(UpdateLifeInsurancePolicyRequest $request, LifeInsurancePolicy $lifeInsurancePolicy): LifeInsurancePolicyResource
    {
        $lifeInsurancePolicy->update($request->validated());

        return new LifeInsurancePolicyResource($lifeInsurancePolicy->refresh());
    }

    public function destroy(LifeInsurancePolicy $lifeInsurancePolicy): LifeInsurancePolicyResource
    {
        $resource = new LifeInsurancePolicyResource($lifeInsurancePolicy);
        $lifeInsurancePolicy->delete();

        return $resource;
    }
}
