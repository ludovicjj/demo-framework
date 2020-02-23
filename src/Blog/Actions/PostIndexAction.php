<?php

namespace App\Blog\Actions;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Framework\Exceptions\NotFoundException;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class PostIndexAction
{
    /** @var PostRepository $postRepository */
    private $postRepository;

    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var CategoryRepository $categoryRepository */
    private $categoryRepository;

    /**
     * PostIndexAction constructor.
     *
     * @param PostRepository $postRepository
     * @param RendererInterface $renderer
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        PostRepository $postRepository,
        RendererInterface $renderer,
        CategoryRepository $categoryRepository
    ) {
        $this->postRepository = $postRepository;
        $this->renderer = $renderer;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     * @throws NotFoundException
     */
    public function index(ServerRequestInterface $request): string
    {
        $param = $request->getQueryParams();
        $page = $param['page'] ?? 1;
        $posts = $this->postRepository->findPaginatedPublic(12, $page);
        $categories = $this->categoryRepository->findAll();

        return $this->renderer->render(
            'blog/index.html.twig',
            [
                'posts' => $posts,
                'categories' => $categories,
            ]
        );
    }
}
