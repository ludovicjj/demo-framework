<?php

namespace App\Blog;

use App\Blog\Actions\BlogAction;
use App\Framework\Module;
use Framework\Router\Router;

class BlogModule extends Module
{
    const DEFINITIONS = __DIR__.'/config.php';

    const MIGRATIONS = __DIR__ . '/phinx/migrations';

    const SEEDS = __DIR__ . '/phinx/seeds';

    public function __construct(
        string $prefix,
        Router $router
    ) {
        $router->get($prefix, [BlogAction::class, 'index'], 'blog.index');
        $router->get($prefix . '/{slug:[a-z0-9\-]+}', [BlogAction::class, 'show'], 'blog.show');
    }
}
