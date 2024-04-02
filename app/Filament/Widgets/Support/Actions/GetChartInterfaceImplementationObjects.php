<?php

namespace App\Filament\Widgets\Support\Actions;

use App\Filament\Widgets\Support\ChartInterface;
use Illuminate\Support\Facades\File;

class GetChartInterfaceImplementationObjects
{


    public function __invoke(): array
    {
        $chartClasses = File::glob(app_path('Filament/Widgets/Charts/**/*.php'));
        $chartObjects = [];

        foreach ($chartClasses as $chartClass) {
            $className = $this->getClassName($chartClass);

            // Check if the class exists and implements ChartInterface
            if (class_exists($className) && in_array(ChartInterface::class, class_implements($className))) {
                $chartObjects[] = app()->make($className);
            }
        }

        return $chartObjects;
    }

    private function getClassName(string $chartClass): string
    {
        return 'App\\' . str_replace(
            ['/', '.php'],
            ['\\', ''],
            substr($chartClass, strlen(app_path()) + 1)
        );
    }

}
