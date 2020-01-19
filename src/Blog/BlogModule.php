<?php

namespace App\Blog;

use App\Framework\Renderer\PHPRenderer;
use Framework\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogModule
{
    /** @var PHPRenderer */
    private $renderer;

    public function __construct(Router $router, PHPRenderer $renderer)
    {
        $this->renderer = $renderer;
        $router->get('/blog', [$this, 'index'], 'blog.index');
        $router->get('/blog/{slug:[a-z\-]+}', [$this, 'show'], 'blog.show');
    }

    public function index(ServerRequestInterface $request): string
    {
        return $this->renderer->render('blog/index.php');
    }

    public function show(ServerRequestInterface $request): string
    {
        return $this->renderer->render(
            'blog/show.php',
            [
                'slug' => $request->getAttribute('slug')
            ]
        );
    }
}
