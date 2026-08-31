<?php

namespace App\Filament\Pages;

use App\Services\ImageOptimizer;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MyProfile extends Page
{
    protected string $view = 'filament.pages.my-profile';

    protected static ?string $title = 'My Profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $user = Auth::user()->load(['memberProfile', 'socialLinks']);

        $this->form->fill([
            'name' => $user->name,
            'email' => $user->email,
            'registration_number' => $user->registration_number,
            'phone' => $user->memberProfile?->phone ?? $user->phone,
            'program' => $user->memberProfile?->program ?? $user->program,
            'faculty' => $user->memberProfile?->faculty,
            'year_of_study' => $user->memberProfile?->year_of_study ?? $user->year_of_study,
            'bio' => $user->memberProfile?->bio ?? $user->bio,
            'headline' => $user->memberProfile?->headline ?? $user->headline,
            'github_username' => $user->socialLinks?->github_username ?? $user->github_username,
            'linkedin_url' => $user->socialLinks?->linkedin_url ?? $user->linkedin_url,
            'discord_username' => $user->socialLinks?->discord_username ?? $user->discord_username,
            'is_discord_member' => $user->socialLinks?->is_discord_member ?? $user->is_discord_member,
            'profile_photo' => $user->profile_photo,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Personal Information')
                    ->columns(2)
                    ->components([
                        FileUpload::make('profile_photo')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                            ->saveUploadedFileUsing(fn (UploadedFile $file): string => app(ImageOptimizer::class)->store($file, 'profile-photos', 800, 800, 84))
                            ->visibility('public')
                            ->label('Profile Photo')
                            ->columnSpanFull(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        TextInput::make('registration_number')
                            ->maxLength(50),
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(30),
                        TextInput::make('program')
                            ->maxLength(255),
                        TextInput::make('faculty')
                            ->maxLength(255),
                        Select::make('year_of_study')
                            ->options([
                                '1' => '1st Year',
                                '2' => '2nd Year',
                                '3' => '3rd Year',
                                '4' => '4th Year',
                                '5' => '5th Year',
                                '6' => '6th Year',
                            ])
                            ->native(false),
                        TextInput::make('headline')
                            ->maxLength(255),
                        Textarea::make('bio')
                            ->maxLength(1000)
                            ->rows(3),
                    ]),

                Section::make('Social Links')
                    ->columns(2)
                    ->components([
                        TextInput::make('github_username')
                            ->maxLength(255)
                            ->label('GitHub Username'),
                        TextInput::make('linkedin_url')
                            ->url()
                            ->maxLength(255)
                            ->label('LinkedIn URL'),
                        Checkbox::make('is_discord_member')
                            ->label('I am a Discord member')
                            ->live(),
                        TextInput::make('discord_username')
                            ->maxLength(255)
                            ->label('Discord Username')
                            ->visible(fn (callable $get): bool => $get('is_discord_member')),
                    ]),

                Section::make('Change Password')
                    ->columns(2)
                    ->components([
                        TextInput::make('current_password')
                            ->password()
                            ->revealable()
                            ->label('Current Password'),
                        TextInput::make('new_password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->label('New Password')
                            ->confirmed(),
                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->revealable()
                            ->label('Confirm New Password'),
                    ]),
            ]);
    }

    public function save(): void
    {
        $user = Auth::user();

        $data = $this->form->getState();

        $this->validate([
            'data.email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        if ($data['email'] !== $user->email) {
            $user->email_verified_at = null;
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'registration_number' => $data['registration_number'] ?? null,
            'discord_username' => $data['discord_username'] ?? null,
            'is_discord_member' => $data['is_discord_member'] ?? false,
        ]);

        if ($data['profile_photo'] && $data['profile_photo'] !== $user->profile_photo) {
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $user->profile_photo = $data['profile_photo'];
        }

        if ($data['current_password'] && $data['new_password']) {
            if (! Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Current password is incorrect')
                    ->danger()
                    ->send();

                return;
            }

            $user->password = Hash::make($data['new_password']);
        }

        $user->save();

        $user->memberProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone' => $data['phone'] ?? null,
                'program' => $data['program'] ?? null,
                'faculty' => $data['faculty'] ?? null,
                'year_of_study' => $data['year_of_study'] ?? null,
                'bio' => $data['bio'] ?? null,
                'headline' => $data['headline'] ?? null,
            ]
        );

        $user->socialLinks()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'github_username' => $data['github_username'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'discord_username' => $data['discord_username'] ?? null,
                'is_discord_member' => $data['is_discord_member'] ?? false,
            ]
        );

        Notification::make()
            ->title('Profile updated successfully.')
            ->success()
            ->send();

        $this->fillForm();
    }

    public function getUserProperty()
    {
        return Auth::user()->load(['memberProfile', 'socialLinks']);
    }

    public function getActivitiesProperty()
    {
        return Auth::user()->actions()->latest()->take(20)->get()
            ->map(fn ($a) => [
                'description' => $a->description,
                'date' => $a->created_at->diffForHumans(),
            ]);
    }

    public function renewMembershipAction(): Action
    {
        return Action::make('renewMembership')
            ->label('Renew Membership')
            ->color('warning')
            ->action(function () {
                $user = Auth::user();
                $user->membership_status = 'active';
                $user->membership_renewed_at = now();
                $user->save();

                Notification::make()
                    ->title('Membership renewed successfully.')
                    ->success()
                    ->send();
            });
    }

    public function viewMembershipCardAction(): Action
    {
        return Action::make('viewMembershipCard')
            ->label('View Membership Card')
            ->color('gray')
            ->url(route('membership.card'), shouldOpenInNewTab: true);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save Changes')
                ->action('save'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'My Profile';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Portal';
    }

    public static function getNavigationSort(): ?int
    {
        return 1;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'super-admin']) ?? false;
    }
}
