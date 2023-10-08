<?php

namespace Rabol\FilamentLogviewer\Pages;

use Closure;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Jackiedo\LogReader\Facades\LogReader;
use Rabol\FilamentLogviewer\Models\LogFile;

class LogViewerViewLogPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament-logviewer::log-viewer-view';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'log-viewer-view-log-page';

    private $logEntries;

    private $log;

    private $fileName;

    public static function getRoutes(): Closure
    {
        return function () {
            $slug = static::getSlug();
            Route::get("{$slug}/{fileName?}", static::class)->name($slug);
        };
    }

    public function getTitle(): string
    {
        return __('filament-logviewer::filament-logviewer.pages.log_file', ['name' => $this->fileName]);
    }

    public function mount(): void
    {
        $fileName = request()->query('fileName');

        $this->log = LogReader::filename($fileName);
        $this->logEntries = $this->log->get(); // we need to paginate...
        $this->fileName = $fileName;
    }

    protected function getActions(): array
    {
        return [
            Action::make('back')
                ->label('Back')
                ->url(LogViewerPage::getUrl()),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'header' => null,
            'footer' => null,
            'logEntries' => $this->logEntries,
            'log' => $this->log,
            'filename' => $this->fileName,
        ];
    }

}
