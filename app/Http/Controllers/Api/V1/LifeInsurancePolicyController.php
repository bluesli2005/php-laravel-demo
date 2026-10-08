<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLifeInsurancePolicyRequest;
use App\Http\Requests\UpdateLifeInsurancePolicyRequest;
use App\Http\Resources\LifeInsurancePolicyResource;
use App\Models\LifeInsurancePolicy;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LifeInsurancePolicyController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LifeInsurancePolicyResource::collection(
            LifeInsurancePolicy::query()->latest()->get()
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
