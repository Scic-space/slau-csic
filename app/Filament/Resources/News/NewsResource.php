<?php

namespace App\Filament\Resources\News;

use App\Filament\Resources\News\Pages\ManageNews;
use App\Models\News;
use App\Models\NewsCategory;
use App\Services\ImageOptimizer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;

class NewsResource extends Resource
{
    protected static ?string $model = News::class;

    protected static ?string $slug = 'manage-news';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->disabled()
                    ->dehydrated(false),
                Textarea::make('excerpt')
                    ->required()
                    ->rows(3)
                    ->maxLength(500)
                    ->helperText('Brief summary shown in the news listing cards.'),
                RichEditor::make('content')
                    ->required(),
                Select::make('category')
                    ->options(collect(NewsCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray())
                    ->required(),
                Select::make('content_type')
                    ->options([
                        'article' => 'Article',
                        'video' => 'Video',
                    ])
                    ->required()
                    ->default('article'),
                TextInput::make('source_name')
                    ->label('Source Name')
                    ->placeholder('e.g. The Hacker News, BleepingComputer')
                    ->maxLength(255),
                TextInput::make('source_url')
                    ->label('Source URL')
                    ->url()
                    ->placeholder('https://...'),
                TextInput::make('thumbnail_url')
                    ->label('Thumbnail URL')
                    ->url()
                    ->placeholder('https://...')
                    ->visible(fn ($get) => ! $get('thumbnail_file')),
                FileUpload::make('thumbnail_file')
                    ->label('Thumbnail Upload')
                    ->disk('public')
                    ->directory('news/thumbnails')
                    ->image()
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9')
                    ->imageResizeTargetWidth('800')
                    ->imageResizeTargetHeight('450')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
                    ->saveUploadedFileUsing(fn (UploadedFile $file): string => app(ImageOptimizer::class)->store($file, 'news/thumbnails', 1280, 720))
                    ->maxSize(2048)
                    ->visible(fn ($get) => $get('content_type') === 'video'),
                TextInput::make('video_url')
                    ->label('YouTube URL')
                    ->url()
                    ->placeholder('https://youtube.com/watch?v=...')
                    ->visible(fn ($get) => $get('content_type') === 'video' && ! $get('video_file')),
                FileUpload::make('video_file')
                    ->label('Video Upload')
                    ->disk('public')
                    ->directory('news/videos')
                    ->acceptedFileTypes(['video/mp4', 'video/webm', 'video/ogg'])
                    ->maxSize(1048576)
                    ->visible(fn ($get) => $get('content_type') === 'video')
                    ->helperText('Upload an MP4, WebM, or OGG file. Max 1 GB.'),
                Toggle::make('is_featured')
                    ->default(false),
                Toggle::make('is_published')
                    ->default(false),
                DateTimePicker::make('published_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof NewsCategory ? $state->label() : $state)
                    ->colors([
                        'danger' => 'threat_intel',
                        'warning' => 'vulnerabilities',
                        'info' => 'policy_compliance',
                        'success' => 'industry',
                        'gray' => 'tools_research',
                    ]),
                TextColumn::make('content_type')
                    ->badge()
                    ->colors([
                        'info' => 'article',
                        'success' => 'video',
                    ]),
                IconColumn::make('is_featured')
                    ->boolean(),
                IconColumn::make('is_published')
                    ->boolean(),
                TextColumn::make('author.name')
                    ->label('Author'),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options(collect(NewsCategory::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray()),
                SelectFilter::make('content_type')
                    ->options([
                        'article' => 'Article',
                        'video' => 'Video',
                    ]),
                Filter::make('is_published')
                    ->query(fn ($query) => $query->where('is_published', true)),
                Filter::make('is_featured')
                    ->query(fn ($query) => $query->where('is_featured', true)),
            ])
            ->defaultSort('published_at', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageNews::route('/'),
        ];
    }
}
