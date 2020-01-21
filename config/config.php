<?php

use App\Framework\Renderer\RendererInterface;
use App\Framework\Renderer\TwigRendererFactory;
use App\Framework\Twig\RouterExtension;
use Framework\Router\Router;
use function DI\create;
use function DI\factory;
use function DI\get;

return [
    'database.host' => 'localhost',
    'database.name' => 'jj_demoframework',
    'database.user' => 'root',
    'database.pass' => '',
    'database.port' => 3306,
    'database.charset' => 'utf8',
    'views.path' => dirname(__DIR__).'/views',
    'twig.extensions' => [
        get(RouterExtension::class)
    ],
    Router::class => create(),
    RendererInterface::class => factory(TwigRendererFactory::class)
];
