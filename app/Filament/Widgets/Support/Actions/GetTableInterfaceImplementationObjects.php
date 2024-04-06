<?php

namespace App\Filament\Widgets\Support\Actions;

use App\Filament\Widgets\Support\ChartInterface;
use App\Filament\Widgets\Support\TableInterface;
use Illuminate\Support\Facades\File;

class GetTableInterfaceImplementationObjects
{
    public function __invoke(): array
    {
        $tableClasses = File::glob(app_path('Filament/Widgets/Tables/**/*.php'));
        $tableObjects = [];

        foreach ($tableClasses as $tableClass) {
            $className = $this->getClassName($tableClass);

            // Check if the class exists and implements ChartInterface
            if (class_exists($className) && in_array(TableInterface::class, class_implements($className))) {
                $tableObjects[] = app()->make($className);
            }
        }

        return $tableObjects;
    }

    private function getClassName(string $tableClass): string
    {
        return 'App\\' . str_replace(
            ['/', '.php'],
            ['\\', ''],
            substr($tableClass, strlen(app_path()) + 1)
        );
    }

}
