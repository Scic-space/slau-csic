<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('import')
                ->label('Import Members')
                ->icon('heroicon-o-arrow-up-tray')
                ->schema([
                    FileUpload::make('file')
                        ->label('CSV/Excel File')
                        ->acceptedFileTypes(['text/csv', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'])
                        ->maxSize(5120)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $file = Storage::disk('public')->path($data['file']);

                    try {
                        Excel::import(new \App\Imports\MembersImport, $file);

                        Notification::make()
                            ->title('Members imported successfully')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import failed: '.$e->getMessage())
                            ->danger()
                            ->send();
                    } finally {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }),

            Action::make('broadcast')
                ->label('Email Members')
                ->icon('heroicon-o-envelope')
                ->schema([
                    TextInput::make('subject')
                        ->label('Subject')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('body')
                        ->label('Message')
                        ->required()
                        ->rows(5),
                ])
                ->action(function (array $data): void {
                    $query = $this->getFilteredQuery();
                    $count = $query->count();

                    $query->chunk(100, function ($users) use ($data): void {
                        foreach ($users as $user) {
                            $user->notify(new \App\Notifications\BroadcastMessage(
                                subject: $data['subject'],
                                body: $data['body'],
                            ));
                        }
                    });

                    Notification::make()
                        ->title("Message sent to {$count} members")
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFilteredQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = User::query();

        $tabModifiers = [
            'pending' => fn (Builder $query) => $query->where('membership_status', 'pending'),
            'active' => fn (Builder $query) => $query->where('membership_status', 'active'),
            'alumni' => fn (Builder $query) => $query->where('membership_type', 'alumni'),
            'suspended' => fn (Builder $query) => $query->where('membership_status', 'suspended'),
            'expiring' => fn (Builder $query) => $query->expiringSoon(),
        ];

        if ($activeTab = $this->activeTab) {
            $modifier = $tabModifiers[$activeTab] ?? null;
            if ($modifier) {
                $modifier($query);
            }
        }

        return $query;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Members')
                ->badge(fn (): int => User::count()),

            'pending' => Tab::make('Pending Approval')
                ->badge(fn (): int => User::where('membership_status', 'pending')->count(), 'warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_status', 'pending')),

            'active' => Tab::make('Active Members')
                ->badge(fn (): int => User::where('membership_status', 'active')->count(), 'success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_status', 'active')),

            'alumni' => Tab::make('Alumni')
                ->badge(fn (): int => User::where('membership_type', 'alumni')->count(), 'gray')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_type', 'alumni')),

            'suspended' => Tab::make('Suspended')
                ->badge(fn (): int => User::where('membership_status', 'suspended')->count(), 'danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('membership_status', 'suspended')),

            'expiring' => Tab::make('Expiring Soon')
                ->badge(fn (): int => User::expiringSoon()->count(), 'warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->expiringSoon()),
        ];
    }
}
