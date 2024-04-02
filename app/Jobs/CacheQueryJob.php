<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheQueryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ?string $queryClassName;
    private ?array $queryData;
    private string $cacheKey;

    /**
     * Create a new job instance.
     */
    public function __construct($query, string $cacheKey)
    {
        $this->cacheKey = $cacheKey;
        $this->queryClassName = get_class($query->first());
        if (Cache::has($this->cacheKey)) {
            $this->queryData = null;
        }

        $this->queryData = $this->getQueryData($query->first());
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        /*Set max execute time of the script to half an hour since some queries are quite slow.*/
        set_time_limit(1800);

        // Check if the cache key exists
        if (Cache::has($this->cacheKey)) {
            return;
        }

        if (!$this->queryData) {
            return;
        }

        // Execute the query and cache the result
        Cache::rememberForever($this->cacheKey, function () {
            return $this->queryData;
        });
    }

    /**
     * Get the data from the query to be stored in the job.
     */
    private function getQueryData($query): ?array
    {
        if ($query instanceof \Illuminate\Database\Query\Builder) {
            return $query->get()->toArray();
        } elseif ($query instanceof \Illuminate\Database\Eloquent\Builder) {
            return $query->get()->toArray();
        }

        return null;
    }

    /**
     * Unserialize the model instance.
     */
    public function __wakeup(): void
    {
        $this->query = app($this->queryClassName);
        $this->query->setBindings($this->queryData['bindings']);
        $this->query->setQuery($this->queryData['sql']);
    }
}
