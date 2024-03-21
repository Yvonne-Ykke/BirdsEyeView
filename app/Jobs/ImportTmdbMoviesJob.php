<?php

namespace App\Jobs;

use App\Api\Actions\Tmdb\ImportTitleDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportTmdbMoviesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    private array $tmdbExternIds = [];

    public function __construct(array $tmdbExternIds = [])
    {
        $this->tmdbExternIds = $tmdbExternIds;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->tmdbExternIds as $tmdbExternId) {
            app(ImportTitleDetail::class)($tmdbExternId);
        }
    }
}
