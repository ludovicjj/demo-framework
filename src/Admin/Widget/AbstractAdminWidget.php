<?php

namespace App\Admin\Widget;

abstract class AbstractAdminWidget implements AdminWidgetInterface
{
    public function getPosition(): int
    {
        return 0;
    }

    public function renderMenu(): string
    {
        return '';
    }

    public function render(): string
    {
        return '';
    }
}
