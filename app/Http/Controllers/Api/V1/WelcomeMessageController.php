<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWelcomeMessageRequest;
use App\Http\Requests\UpdateWelcomeMessageRequest;
use App\Http\Resources\WelcomeMessageResource;
use App\Models\WelcomeMessage;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class WelcomeMessageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return WelcomeMessageResource::collection(
            WelcomeMessage::query()->whereIn('page', WelcomeMessage::PAGES)->orderBy('page')->get()
        );
    }

    public function store(StoreWelcomeMessageRequest $request): WelcomeMessageResource
    {
        try {
            $message = WelcomeMessage::query()->create($request->validated());
        } catch (UniqueConstraintViolationException $exception) {
            throw ValidationException::withMessages(['page' => 'The page has already been taken.']);
        }

        return new WelcomeMessageResource($message);
    }

    public function show(WelcomeMessage $page): WelcomeMessageResource
    {
        return new WelcomeMessageResource($page);
    }

    public function update(UpdateWelcomeMessageRequest $request, WelcomeMessage $page): WelcomeMessageResource
    {
        $page->update($request->safe()->only('content'));

        return new WelcomeMessageResource($page);
    }

    public function destroy(WelcomeMessage $page): WelcomeMessageResource
    {
        $page->delete();

        return new WelcomeMessageResource($page);
    }
}
