<?php

namespace App\Http\Requests;

use App\Models\WelcomeMessage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWelcomeMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'page' => ['bail', 'required', 'string', Rule::in(WelcomeMessage::PAGES), Rule::unique(WelcomeMessage::class, 'page')],
            'content' => ['required', 'string', 'max:255'],
        ];
    }
}
