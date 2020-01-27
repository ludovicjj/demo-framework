<?php

namespace App\Blog;

use App\Blog\Actions\BlogAction;
use Framework\Module;
use Framework\Router\Router;
use Psr\Container\ContainerInterface;

class BlogModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';

    const MIGRATIONS = __DIR__ . '/phinx/migrations';

    const SEEDS = __DIR__ . '/phinx/seeds';

    public function __construct(ContainerInterface $container)
    {
        $router = $container->get(Router::class);
        $router->get(
            $container->get('blog.prefix'),
            [BlogAction::class, 'index'],
            'blog.index'
        );
        $router->get(
            $container->get('blog.prefix') . '/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            [BlogAction::class, 'show'],
            'blog.show'
        );
    }
}
