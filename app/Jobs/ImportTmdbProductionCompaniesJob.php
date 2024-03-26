<?php

namespace App\Jobs;

use App\Api\Actions\Tmdb\ImportProductionCompanyDetail;
use App\Api\Actions\Tmdb\ImportTitleDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportTmdbProductionCompaniesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
            app(ImportProductionCompanyDetail::class)($tmdbExternId);
        }
    }
}
