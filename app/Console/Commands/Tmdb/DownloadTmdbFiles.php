<?php

namespace App\Console\Commands\Tmdb;

use App\Console\Commands\Actions\DeCompressFiles;
use App\Console\Commands\Support\Enums\TmdbFileEndpoints;
use App\Support\Actions\FindOrCreateDirectory;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class DownloadTmdbFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-tmdb-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $baseUrl = 'http://files.tmdb.org/p/exports/';

    private array $endpoints = [];

    private array $files = [];

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $this->endpoints = TmdbFileEndpoints::values();

        app(FindOrCreateDirectory::class)('/tmdb_files/compressed');
        app(FindOrCreateDirectory::class)('/tmdb_files/decompressed');
        $this->downloadFiles();
        app(DeCompressFiles::class)($this->files);
    }

    /**
     * @throws GuzzleException
     */
    private function downloadFiles(): void
    {
        $client = new Client();
        foreach ($this->endpoints as $endpoint) {
            $response = $client->request('GET', $this->getFileUrl($endpoint), [
                'sink' => storage_path("app/tmdb_files/compressed/{$this->getFileName($endpoint)}")
            ]);

            if ($response->getStatusCode() == 200) {
                $this->files[] = "tmdb_files/compressed/{$this->getFileName($endpoint)}";
                $this->info('File downloaded successfully!');
            } else {
                $this->error('Failed to download file.');
            }
        }
    }

    private function getDateString(): string
    {
        $date = Carbon::now()->subDay();
        return $date->format('m') . '_' . $date->format('d') . '_' . $date->format('Y');
    }

    private function getFileUrl(string $endpoint): string
    {
        return $this->baseUrl . $endpoint . $this->getDateString() . '.json.gz';
    }

    private function getFileName(string $endpoint): string
    {
        return $endpoint . $this->getDateString() . '.json.gz';
    }


}
