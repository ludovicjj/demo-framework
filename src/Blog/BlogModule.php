<?php

namespace App\Blog;

use App\Blog\Actions\CategoryShowAction;
use App\Blog\Actions\PostIndexAction;
use App\Blog\Actions\PostShowAction;
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
        //recuperation du router
        $router = $container->get(Router::class);

        //Ajoute les routes, method="GET"
        $router->get(
            $container->get('blog.prefix'),
            [PostIndexAction::class, 'index'],
            'blog.index'
        );
        $router->get(
            $container->get('blog.prefix') . '/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            [PostShowAction::class, 'show'],
            'blog.show'
        );
        $router->get(
            $container->get('blog.prefix') . '/category/{slug:[a-z0-9\-]+}',
            [CategoryShowAction::class, 'show'],
            'category.show'
        );
    }
}
