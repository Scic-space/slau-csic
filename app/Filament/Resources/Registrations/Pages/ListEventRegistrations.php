<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\EventRegistrationResource;
use Filament\Resources\Pages\ListRecords;

class ListEventRegistrations extends ListRecords
{
    protected static string $resource = EventRegistrationResource::class;
}
