<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

class DiscordSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationLabel = 'Discord';

    protected static ?string $title = 'Discord Integration';

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'discord-settings';

    protected string $view = 'filament.pages.discord-settings';

    public ?string $webhook_url = null;

    public ?string $discord_invite_url = null;

    public ?string $whatsapp_invite_url = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }

    public function mount(): void
    {
        $this->webhook_url = Setting::getValue('discord_announcement_webhook', '');
        $this->discord_invite_url = Setting::getValue('discord_invite_url', '');
        $this->whatsapp_invite_url = Setting::getValue('whatsapp_invite_url', '');
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Discord Webhook')
                ->description('Configure the Discord webhook URL for auto-posting announcements.')
                ->schema([
                    Forms\Components\TextInput::make('webhook_url')
                        ->label('Webhook URL')
                        ->url()
                        ->placeholder('https://discord.com/api/webhooks/...')
                        ->helperText('The Discord channel webhook URL. Get this from Discord > Channel Settings > Integrations > Webhooks.'),
                ]),
            Forms\Components\Section::make('Community Links')
                ->description('Invite URLs shown in the sidebar widget.')
                ->schema([
                    Forms\Components\TextInput::make('discord_invite_url')
                        ->label('Discord Invite URL')
                        ->url()
                        ->placeholder('https://discord.gg/...')
                        ->helperText('Discord server invite URL shown in the sidebar.'),
                    Forms\Components\TextInput::make('whatsapp_invite_url')
                        ->label('WhatsApp Invite URL')
                        ->url()
                        ->placeholder('https://chat.whatsapp.com/...')
                        ->helperText('WhatsApp group invite URL shown in the sidebar.'),
                ]),
        ]);
    }

    public function save(): void
    {
        $this->validate();

        Setting::setValue('discord_announcement_webhook', $this->webhook_url);
        Setting::setValue('discord_invite_url', $this->discord_invite_url);
        Setting::setValue('whatsapp_invite_url', $this->whatsapp_invite_url);

        Notification::make()
            ->title('Discord settings saved')
            ->success()
            ->send();
    }
}
