<?php

namespace App\Console\Commands\Imdb;

use App\Console\Commands\Support\Actions\DeCompressFiles;
use App\Console\Commands\Support\Enums\ImdbFileEndpoints;
use App\Support\Actions\FindOrCreateStorageDirectory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Console\Command;

class DownloadImdbFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-imdb-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private string $baseDirectory = '/imdb_files/';

    private string $baseUrl = "https://datasets.imdbws.com/";

    private array $files = [];

    /**
     * Execute the console command.
     * @throws GuzzleException
     */
    public function handle(): void
    {
        $this->endpoints = ImdbFileEndpoints::values();

        app(FindOrCreateStorageDirectory::class)($this->baseDirectory . 'compressed');
        app(FindOrCreateStorageDirectory::class)($this->baseDirectory . 'decompressed');
        $this->downloadFiles();

        app(DeCompressFiles::class)($this->files, true);
    }

    /**
     * @throws GuzzleException
     */
    private function downloadFiles(): void
    {
        $client = new Client();
        foreach ($this->endpoints as $endpoint) {
            $response = $client->request('GET', $this->getFileUrl($endpoint), [
                'sink' => storage_path("app{$this->baseDirectory}compressed/$endpoint")
            ]);

            if ($response->getStatusCode() == 200) {
                $this->files[] = $this->baseDirectory . "compressed/$endpoint";
                $this->info('File downloaded ' . $endpoint . ' successfully!');
            } else {
                $this->error('Failed to download ' . $endpoint . '.');
            }
        }
    }

    private function getFileUrl(mixed $endpoint): string
    {
        return $this->baseUrl . $endpoint;
    }

}
