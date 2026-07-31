<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionApiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'start_date' => $this->start_date?->toISOString(),
            'end_date' => $this->end_date?->toISOString(),
            'location' => $this->location,
            'website_url' => $this->website_url,
            'is_team_based' => $this->is_team_based,
            'max_team_size' => $this->max_team_size,
            'participation_status' => $this->participation_status,
            'club_ranking' => $this->club_ranking,
            'achievements' => $this->achievements,
            'participants_count' => $this->whenCounted('participants'),
            'participants' => ParticipantApiResource::collection($this->whenLoaded('members')),
            'status' => $this->statusLabel(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
