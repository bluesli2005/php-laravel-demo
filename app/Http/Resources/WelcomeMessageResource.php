<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WelcomeMessageResource extends JsonResource
{
    /** @return array{page: string, content: string} */
    public function toArray(Request $request): array
    {
        return [
            'page' => $this->page,
            'content' => $this->content,
        ];
    }
}
