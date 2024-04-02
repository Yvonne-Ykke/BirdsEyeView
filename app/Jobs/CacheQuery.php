<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class CacheQuery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Builder $query;
    private string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct(Builder $query, string $cacheKey)
    {
        $this->query = $query;
        $this->cacheKey = $cacheKey;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*Set max execute time of the script to half an hour since some queries are quite slow.*/
        set_time_limit(1800);
        Cache::rememberForever($this->cacheKey, function () {
            $this->query
                ->get()
                ->toArray();
        });
    }
}
