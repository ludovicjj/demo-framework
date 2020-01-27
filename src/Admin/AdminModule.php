<?php

namespace App\Admin;

use App\Admin\Actions\AdminPostsAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use Psr\Container\ContainerInterface;

class AdminModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';

    public function __construct(ContainerInterface $container)
    {
        $container->get(RendererInterface::class)->addPath(__DIR__ . '/views', 'admin');
        $router = $container->get(Router::class);
        $adminPrefix = $container->get('admin.prefix');
        $router->crud("$adminPrefix/posts", AdminPostsAction::class, 'admin.posts');
    }
}
