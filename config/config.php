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
    RendererInterface::class => factory(TwigRendererFactory::class),
    PDO::class => function (\Psr\Container\ContainerInterface $container) {
        $dsn = 'mysql:dbname='. $container->get('database.name');
        $dsn .= ';host='. $container->get('database.host');
        $dsn .= ';charset=' . $container->get('database.charset');
        return new PDO(
            $dsn,
            $container->get('database.user'),
            $container->get('database.pass'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    }
];
