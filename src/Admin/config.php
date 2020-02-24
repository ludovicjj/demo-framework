<?php

use App\Admin\AdminModule;
use App\Admin\Actions\DashboardAction;
use App\Admin\Twig\AdminMenuExtension;
use App\Admin\Widget\CategoryWidget;
use App\Admin\Widget\MenuWidget;
use App\Admin\Widget\PostWidget;
use function DI\autowire;
use function DI\create;
use function DI\get;

return [
    'admin.prefix' => '/admin',
    'admin.widget' => [
        get(PostWidget::class),
        get(CategoryWidget::class),
        get(MenuWidget::class)
    ],
    AdminModule::class => autowire()->constructorParameter('adminPrefix', get('admin.prefix')),
    DashboardAction::class => autowire()->constructorParameter('widgets', get('admin.widget')),
    AdminMenuExtension::class => create()->constructor(get('admin.widget'))
];
