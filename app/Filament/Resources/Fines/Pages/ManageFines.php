<?php

namespace App\Filament\Resources\Fines\Pages;

use App\Filament\Resources\Fines\FineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ManageFines extends ManageRecords
{
    protected static string $resource = FineResource::class;

    #[Url]
    public ?int $user = null;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        if ($this->user) {
            $query->where('user_id', $this->user);
        }

        return $query;
    }
}
