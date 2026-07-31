<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'team_name' => $this->pivot->team_name ?? null,
            'role' => $this->pivot->role ?? null,
            'joined_at' => $this->pivot->created_at?->toISOString(),
        ];
    }
}
