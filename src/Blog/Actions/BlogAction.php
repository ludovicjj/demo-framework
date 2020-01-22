<?php

namespace App\Blog\Actions;

use App\Blog\Repository\PostRepository;
use App\Framework\Exceptions\NotFoundException;
use App\Framework\Renderer\RendererInterface;
use App\Framework\Response\RedirectResponse;
use Framework\Router\Router;
use Psr\Http\Message\ServerRequestInterface;

class BlogAction
{
    /** @var RendererInterface */
    private $renderer;

    /** @var Router */
    private $router;

    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * BlogAction constructor.
     * @param RendererInterface $renderer
     * @param Router $router
     * @param PostRepository $postRepository
     */
    public function __construct(
        RendererInterface $renderer,
        Router $router,
        PostRepository $postRepository
    ) {
        $this->renderer = $renderer;
        $this->router = $router;
        $this->postRepository = $postRepository;
    }

    /**
     * @return string
     */
    public function index(): string
    {
        $posts = $this->postRepository->findPaginated();

        return $this->renderer->render(
            'blog/index.html.twig',
            ['posts' => $posts]
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     * @throws NotFoundException
     */
    public function show(ServerRequestInterface $request)
    {
        $post = $this->postRepository->find((int)$request->getAttribute('id'));

        if (!$post) {
            throw new NotFoundException(
                sprintf(
                    'Not found entity with id : %d',
                    (int)$request->getAttribute('id')
                )
            );
        }

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
