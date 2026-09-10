<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\RelationManagers\ActivitiesRelationManager;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Users';

    protected static string|UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getViewForm(): Closure
    {
        return function (Schema $schema): Schema {
            return $schema
                ->components([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->disabled(),
                            TextInput::make('email')
                                ->disabled(),
                            TextInput::make('registration_number')
                                ->label('Registration Number')
                                ->disabled(),
                            Select::make('roles')
                                ->label('Roles')
                                ->multiple()
                                ->relationship('roles', 'name')
                                ->preload()
                                ->disabled(),
                            Select::make('membership_type')
                                ->disabled()
                                ->options([
                                    'active' => 'Active Member',
                                    'associate' => 'Associate Member',
                                    'alumni' => 'Alumni',
                                ]),
                            Select::make('membership_status')
                                ->disabled()
                                ->options([
                                    'active' => 'Active',
                                    'pending' => 'Pending Approval',
                                    'suspended' => 'Suspended',
                                    'inactive' => 'Inactive',
                                ]),
                            Select::make('intake')
                                ->disabled()
                                ->options([
                                    'august' => 'August',
                                    'january' => 'January',
                                    'february' => 'February',
                                    'may' => 'May',
                                ]),
                            Select::make('intake_year')
                                ->disabled()
                                ->options(fn (): array => collect(range(now()->year - 5, now()->year))
                                    ->mapWithKeys(fn (int $year): array => [$year => (string) $year])
                                    ->all()),
                            DatePicker::make('joined_at')
                                ->disabled(),
                            DatePicker::make('approved_at')
                                ->disabled(),
                            DatePicker::make('membership_expires_at')
                                ->label('Membership Expires')
                                ->disabled(),
                            DateTimePicker::make('membership_renewed_at')
                                ->label('Last Renewed')
                                ->disabled(),
                        ]),

                    Section::make('Member Profile')
                        ->relationship('memberProfile')
                        ->columns(2)
                        ->schema([
                            TextInput::make('phone')
                                ->tel()
                                ->disabled(),
                            TextInput::make('faculty')
                                ->disabled(),
                            TextInput::make('program')
                                ->label('Program/Course')
                                ->disabled(),
                            TextInput::make('year_of_study')
                                ->numeric()
                                ->disabled(),
                            DatePicker::make('date_of_birth')
                                ->label('Date of Birth')
                                ->disabled(),
                            TextInput::make('gender')
                                ->disabled(),
                            TextInput::make('residence')
                                ->disabled(),
                            TextInput::make('emergency_contact_name')
                                ->label('Emergency Contact Name')
                                ->disabled(),
                            TextInput::make('emergency_contact_phone')
                                ->label('Emergency Contact Phone')
                                ->disabled(),
                            TextInput::make('headline')
                                ->disabled(),
                            Textarea::make('bio')
                                ->disabled()
                                ->columnSpanFull(),
                        ]),

                    Section::make('Account & Administration')
                        ->columns(2)
                        ->schema([
                            TextInput::make('github_username')
                                ->label('GitHub Username')
                                ->disabled(),
                            TextInput::make('linkedin_url')
                                ->label('LinkedIn URL')
                                ->disabled(),
                            TextInput::make('discord_username')
                                ->label('Discord Username')
                                ->disabled(),
                            Textarea::make('approval_notes')
                                ->label('Approval/Rejection Notes')
                                ->disabled(),
                            Textarea::make('admin_notes')
                                ->label('Admin Notes')
                                ->disabled(),
                            Textarea::make('suspension_reason')
                                ->label('Suspension Reason')
                                ->disabled(),
                            DateTimePicker::make('suspended_until')
                                ->label('Suspended Until')
                                ->disabled(),
                        ]),
                ]);
        };
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'registration_number'];
    }
}
