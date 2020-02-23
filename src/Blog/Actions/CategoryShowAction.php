<?php

namespace App\Blog\Actions;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Exceptions\NotFoundException;

class CategoryShowAction
{
    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var CategoryRepository $categoryRepository */
    private $categoryRepository;

    /** @var PostRepository $postRepository */
    private $postRepository;

    public function __construct(
        RendererInterface $renderer,
        CategoryRepository $categoryRepository,
        PostRepository $postRepository
    ) {
        $this->renderer = $renderer;
        $this->categoryRepository = $categoryRepository;
        $this->postRepository = $postRepository;
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     * @throws NotFoundException
     */
    public function show(ServerRequestInterface $request): string
    {
        $queryParams =$request->getQueryParams();
        $page = $queryParams['page'] ?? 1;

        $category = $this->categoryRepository->findOneBy(['slug' => $request->getAttribute('slug')]);
        $posts = $this->postRepository->findPaginatedPublicForCategory(6, $page, $category->id);
        $categories = $this->categoryRepository->findAll();

        return $this->renderer->render(
            'category/show.html.twig',
            [
                'posts' => $posts,
                'category' => $category,
                'categories' => $categories,
            ]
        );
    }
}
