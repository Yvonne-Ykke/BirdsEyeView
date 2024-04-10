<?php

namespace App\Filament\Widgets\Custom;

use Filament\Widgets\Widget;

class CanaryLogoWidget extends Widget
{
    protected static string $view = 'filament.widgets.canary-logo-widget';

    protected int | string | array $columnSpan = 1;
}
