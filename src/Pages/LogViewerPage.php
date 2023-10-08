<?php

namespace Rabol\FilamentLogviewer\Pages;


use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Jackiedo\LogReader\Facades\LogReader;
use Rabol\FilamentLogviewer\Models\LogFile;

class LogViewerPage extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-logviewer::log-viewer';
    protected static ?string $model = LogFile::class;
    protected static ?string $slug = 'log-viewer-page';

    public function getTitle(): string
    {
        return __('filament-log-viewer::filament-logviewer.pages.log_viewer');
    }

    protected static function getNavigationLabel(): string
    {
        return __('filament-log-viewer::filament-logviewer.pages.log_viewer');
    }

    public static function getNavigationGroup(): ?string
    {
        return config('filament-logviewer.navigation_group', null);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('filament-logviewer::filament-logviewer.fields.name'))
                    ->searchable()
                    ->sortable(),
            ])->query(LogFile::query())
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('View')
                    ->url(function (LogFile $record) {
                        return LogViewerViewLogPage::getUrl(['fileName' => $record->name]);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->action(fn(LogFile $record) => $this->deleteLogFile($record))
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => ! static::canDelete($record)),
            ]);
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::check('delete', $record);
    }

    public function deleteLogFile(LogFile $record): void
    {
        $log = LogReader::filename($record->name);
        $deleted = $log->delete();
        LogReader::removeLogFile();
        LogFile::boot();

    }
}
