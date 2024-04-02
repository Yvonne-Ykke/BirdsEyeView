<?php

namespace App\Filament\Widgets\Support\Actions;

use App\Filament\Widgets\Support\ChartInterface;
use Illuminate\Support\Facades\File;

class GetChartInterfaceImplementations
{

    public function __invoke(): array
    {
        $charts = [];

        // Recursively get all PHP files in the specified directory and its subdirectories
        $files = $this->getChartFiles(app_path('Filament/Widgets/Charts'));

        foreach ($files as $file) {
            $class = $this->getClassFromFile($file);

            // Check if the class implements ChartInterface
            if (is_subclass_of($class, ChartInterface::class)) {
                $charts[] = app()->make($class);
            }
        }

        return $charts;
    }

    protected function getChartFiles($directory): array
    {
        $files = [];

        // Get all PHP files in the specified directory
        $directoryFiles = File::files($directory);

        foreach ($directoryFiles as $file) {
            $files[] = $file;
        }

        // Get all subdirectories
        $subdirectories = File::directories($directory);

        foreach ($subdirectories as $subdirectory) {
            $files = array_merge($files, $this->getChartFiles($subdirectory));
        }

        return $files;
    }

    protected function getClassFromFile($file)
    {
        return 'App\\' . str_replace(
                [app_path(), '/', '.php'],
                ['', '\\', ''],
                $file
            );
    }
}
