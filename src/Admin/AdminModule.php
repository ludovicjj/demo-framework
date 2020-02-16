<?php

namespace App\Admin;

use App\Admin\Actions\CategoryCrudAction;
use App\Admin\Actions\PostCrudAction;
use Framework\Module;
use Framework\Router\Router;
use Psr\Container\ContainerInterface;

class AdminModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';

    public function __construct(ContainerInterface $container)
    {
        $router = $container->get(Router::class);
        $adminPrefix = $container->get('admin.prefix');

        //TODO Register all routes to posts CRUD
        $router->crud(
            "$adminPrefix/posts",
            PostCrudAction::class,
            'admin.posts'
        );

        //TODO Register all routes to categories CRUD
        $router->crud(
            "$adminPrefix/categories",
            CategoryCrudAction::class,
            'admin.categories'
        );
    }
}
