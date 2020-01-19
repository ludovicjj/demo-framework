<?php

namespace App\Blog;

use Framework\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogModule
{
    public function __construct(Router $router)
    {
        $router->get('/blog', [$this, 'index'], 'blog.index');
        $router->get('/blog/{slug:[a-z\-]+}', [$this, 'show'], 'blog.show');
    }

    public function index(ServerRequestInterface $request): string
    {
        return '<h1>Bienvenue sur l\'accueil du Blog</h1>';
    }

    public function show(ServerRequestInterface $request): string
    {
        $slug = $request->getAttribute('slug');
        return '<h1>Bienvenue sur le post ' . $slug . '</h1>';
    }
}
