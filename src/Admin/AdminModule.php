<?php

namespace App\Admin;

use App\Admin\Actions\CategoryCrudAction;
use App\Admin\Actions\DashboardAction;
use App\Admin\Actions\PostCrudAction;
use App\Admin\Twig\AdminMenuExtension;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Renderer\TwigRenderer;
use Framework\Router\Router;

class AdminModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        string $adminPrefix,
        AdminMenuExtension $adminMenuExtension
    ) {
        //TODO add route to dashboard
        $router->get(
            $adminPrefix,
            [DashboardAction::class, 'dashboard'],
            'admin.dashboard'
        );

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

        //TODO add extension when twig is initialized to avoid circular dependency
        if ($renderer instanceof TwigRenderer) {
            $renderer->getTwig()->addExtension($adminMenuExtension);
        }
    }
}
