<?php

namespace App\Console\Commands\Tmdb;

use App\Console\Commands\Actions\ProcessMovieTmdbFile;
use App\Console\Commands\Actions\ProcessProductionCompanyFile;
use App\Console\Commands\Support\Enums\TmdbFileEndpoints;
use App\Support\Actions\GetFilesFromDirectory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ImportMoviesFromTmdbFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-movies-from-tmdb-files {--only=} {--skip=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private array $files;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('app:download-tmdb-files');
        $this->files = app(GetFilesFromDirectory::class)('/tmdb_files/decompressed');

        if ($this->checkOnlyConsoleOption()) {
            return;
        }

        $this->processFiles();
        activity()->log('Import Tmdb-data');
    }

    private function processFiles(): void
    {
        foreach ($this->files as $file) {
            if ($this->matchMoviesFile($file)) {
                continue;
            }

            if ($this->matchCompaniesFile($file)) {
                continue;
            }
        }
    }

    private function matchMoviesFile(string $file): bool
    {
        if ($this->option('skip')) {
            if (str_contains(haystack: TmdbFileEndpoints::MOVIES->value, needle: $this->option('only'))) {
                return false;
            }
        }

        if (str_contains(haystack: $file, needle: TmdbFileEndpoints::MOVIES->value)) {
            app(ProcessMovieTmdbFile::class)($file);
            return true;
        }
        return false;
    }

    private function matchCompaniesFile(string $file): bool
    {
        if ($this->option('skip')) {
            if (str_contains(haystack: TmdbFileEndpoints::PRODUCTION_COMPANIES->value, needle: $this->option('only'))) {
                return false;
            }
        }

        if (str_contains(haystack: $file, needle: TmdbFileEndpoints::PRODUCTION_COMPANIES->value)) {
            app(ProcessProductionCompanyFile::class)($file);
            return true;
        }
        return false;
    }

    private function getFileWithEnum(TmdbFileEndpoints $endpoint) {
        foreach ($this->files as $file) {
            if (str_contains(haystack: $file, needle: $endpoint->value)) {
                return $file;
            }
        }
        return '';
    }
    private function checkOnlyConsoleOption(): bool
    {
        if (!$this->option('only')) {
            return false;
        }

        if (str_contains(haystack: TmdbFileEndpoints::PRODUCTION_COMPANIES->value, needle: $this->option('only'))) {
            app(ProcessProductionCompanyFile::class)($this->getFileWithEnum(TmdbFileEndpoints::PRODUCTION_COMPANIES));
            return true;
        }

        if (str_contains(haystack: TmdbFileEndpoints::MOVIES->value, needle: $this->option('only'))) {
            app(ProcessMovieTmdbFile::class)($this->getFileWithEnum(TmdbFileEndpoints::MOVIES));
            return true;
        }

        return false;
    }


}
