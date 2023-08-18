<?php

namespace Rabol\FilamentLogviewer\Pages;

use Closure;
use Filament\Tables;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Jackiedo\LogReader\Facades\LogReader;
use Rabol\FilamentLogviewer\Models\LogFile;
use Filament\Tables\Concerns\InteractsWithTable;

class LogViewerPage extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-log-viewer::log-viewer';
    protected static ?string $model = LogFile::class;

    public function getTitle(): string
    {
        return __('filament-logviewer::pages.log_viewer');
    }

    protected static function getNavigationGroup(): ?string
    {
        return config('filament-log-viewer.navigation_group', null);
    }

    protected function getTableRecordUrlUsing(): Closure
    {
        return fn (Model $record): string => LogViewerViewLogPage::getUrl(['fileName' => $record->name]);
    }

    protected function getTableQuery(): Builder
    {
        return LogFile::query();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('filament-logviewer::fields.name'))
                ->searchable()
                ->sortable()
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('viewlogfile')
                ->label('View')
                ->url(function (LogFile $record) {
                    return LogViewerViewLogPage::getUrl(['fileName' => $record->name]);
                }),
            Tables\Actions\Action::make('delete')
                    ->action('deleteLogFile')
                    ->requiresConfirmation()
                    ->hidden(fn ($record) => ! static::canDelete($record)),
        ];
    }

    public static function canDelete(Model $record): bool
    {
        return Gate::check('delete', $record);
    }

    public function deleteLogfile($record)
    {
        $log = LogReader::filename($record->name);
        $deleted = $log->delete();
        LogReader::removeLogFile();
        LogFile::boot();
    }
}
