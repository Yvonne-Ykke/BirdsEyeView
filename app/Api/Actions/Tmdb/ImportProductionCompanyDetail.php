<?php

namespace App\Api\Actions\Tmdb;

use App\Api\Tmdb\TmdbApi;
use App\Models\ProductionCompany;
use Illuminate\Support\Facades\Log;

class ImportProductionCompanyDetail
{
    private string $endpoint = '3/company/';
    private array|string $result = [];
    private ProductionCompany $company;

    public function __invoke(int $tmbdExternId): void
    {
        $this->result = app(TmdbApi::class)($this->endpoint . $tmbdExternId);

        if (is_string($this->result)) {
            Log::error($this->result);
            return;
        }

        $this->company = $this->saveCompany();
    }

    private function saveCompany()
    {
        return ProductionCompany::updateOrCreate(
            [
                'tmdb_externid' => $this->result['id']
            ],
            [
                'tmdb_externid' => $this->result['id'],
                'name' => $this->result['name'],
                'origin_country' => $this->result['origin_country'],
            ]
        );
    }

}
