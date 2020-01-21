<?php

namespace App\Blog\Actions;

use App\Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class BlogAction
{
    /** @var RendererInterface */
    private $renderer;

    public function __construct(
        RendererInterface $renderer
    ) {
        $this->renderer = $renderer;
    }

    public function index(): string
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
