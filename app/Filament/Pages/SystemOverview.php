<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class SystemOverview extends Page
{
    protected string $view = 'filament.pages.system-overview';

    protected static ?string $title = 'System Overview';

    public static function getNavigationLabel(): string
    {
        return 'System Overview';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'System';
    }

    public static function getNavigationSort(): ?int
    {
        return 8;
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return 'heroicon-o-server-stack';
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function clearCacheAction(): Action
    {
        return Action::make('clearCache')
            ->label('Clear Application Cache')
            ->action(function () {
                try {
                    Artisan::call('cache:clear');
                    Notification::make()->title('Application cache cleared successfully.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to clear cache: '.$e->getMessage())->danger()->send();
                }
            });
    }

    public function clearConfigAction(): Action
    {
        return Action::make('clearConfig')
            ->label('Clear Config Cache')
            ->action(function () {
                try {
                    Artisan::call('config:clear');
                    Notification::make()->title('Config cache cleared successfully.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to clear config: '.$e->getMessage())->danger()->send();
                }
            });
    }

    public function clearViewAction(): Action
    {
        return Action::make('clearView')
            ->label('Clear View Cache')
            ->action(function () {
                try {
                    Artisan::call('view:clear');
                    Notification::make()->title('View cache cleared successfully.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to clear view cache: '.$e->getMessage())->danger()->send();
                }
            });
    }

    public function clearRouteAction(): Action
    {
        return Action::make('clearRoute')
            ->label('Clear Route Cache')
            ->action(function () {
                try {
                    Artisan::call('route:clear');
                    Notification::make()->title('Route cache cleared successfully.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to clear route cache: '.$e->getMessage())->danger()->send();
                }
            });
    }

    public function optimizeAction(): Action
    {
        return Action::make('optimize')
            ->label('Optimize Application')
            ->action(function () {
                try {
                    Artisan::call('optimize');
                    Notification::make()->title('Application optimized successfully.')->success()->send();
                } catch (\Exception $e) {
                    Notification::make()->title('Failed to optimize: '.$e->getMessage())->danger()->send();
                }
            });
    }

    public function downloadBackupAction(): Action
    {
        return Action::make('downloadBackup')
            ->label('Download Backup')
            ->icon('heroicon-o-arrow-down-tray')
            ->action(function () {
                $dbPath = config('database.connections.sqlite.database');

                if (! $dbPath || ! file_exists($dbPath)) {
                    Notification::make()
                        ->title('Database backup is only available for SQLite.')
                        ->warning()
                        ->send();

                    return;
                }

                return response()->download($dbPath, 'database-backup-'.now()->format('Y-m-d-His').'.sqlite');
            });
    }

    public function getSystemInfo(): array
    {
        return [
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
            'Database' => config('database.default'),
            'Cache Driver' => config('cache.default'),
            'Queue Driver' => config('queue.default'),
            'Session Driver' => config('session.driver'),
            'Timezone' => config('app.timezone'),
        ];
    }
}
