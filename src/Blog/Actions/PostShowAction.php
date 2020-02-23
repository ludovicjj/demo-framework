<?php

namespace App\Blog\Actions;

use App\Repository\PostRepository;
use Framework\Exceptions\NotFoundException;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class PostShowAction
{
    /** @var PostRepository $postRepository */
    private $postRepository;

    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var Router $router */
    private $router;

    public function __construct(
        PostRepository $postRepository,
        RendererInterface $renderer,
        Router $router
    ) {
        $this->postRepository = $postRepository;
        $this->renderer = $renderer;
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     * @throws NotFoundException
     */
    public function show(ServerRequestInterface $request)
    {
        $post = $this->postRepository->findWithCategory(['id' => (int)$request->getAttribute('id')]);

        if ($post->slug !== $request->getAttribute('slug')) {
            return new RedirectResponse(
                $this->router->generateUri('blog.show', ['slug' => $post->slug, 'id' => $post->id])
            );
        }

        return $this->renderer->render(
            'blog/show.html.twig',
            ['post' => $post]
        );
    }
}
