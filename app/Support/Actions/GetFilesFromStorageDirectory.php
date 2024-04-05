<?php

namespace App\Support\Actions;

use Illuminate\Support\Facades\Storage;

class GetFilesFromStorageDirectory
{
    public function __invoke($directory): array
    {

        // Check if the directory exists
        if (!Storage::exists($directory)) {
            echo("Directory '{$directory}' does not exist.\n");
            return [];
        }

        $files = Storage::files($directory);

        if (count($files) === 0) {
            echo("No files found in '{$directory}'. \n");
        } else {
            return $files;
        }

        return [];
    }
}
