<?php

namespace App\Support\Actions;

use Illuminate\Support\Facades\Storage;

class FindOrCreateStorageDirectory
{
    public function __invoke(string $directory): void
    {
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
            echo ("Directory '{$directory}' created successfully.\n");
        } else {
            echo ("Directory '{$directory}' already exists.\n");
        }
    }
}
