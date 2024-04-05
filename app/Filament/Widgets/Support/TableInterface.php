<?php

namespace App\Filament\Widgets\Support;

interface TableInterface extends WidgetInterface
{
    function getTableData(): array;
}
