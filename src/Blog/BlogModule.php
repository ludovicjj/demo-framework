<?php

namespace App\Blog;

use App\Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogModule
{
    /** @var RendererInterface */
    private $renderer;

    public function __construct(Router $router, RendererInterface $renderer)
    {
        $this->renderer = $renderer;
        $router->get('/blog', [$this, 'index'], 'blog.index');
        $router->get('/blog/{slug:[a-z0-9\-]+}', [$this, 'show'], 'blog.show');
    }

    public function index(ServerRequestInterface $request): string
    {
        return $this->renderer->render('blog/index.html.twig');
    }

    public function show(ServerRequestInterface $request): string
    {
        return $this->renderer->render(
            'blog/show.html.twig',
            [
                'slug' => $request->getAttribute('slug')
            ]
        );
    }
}
