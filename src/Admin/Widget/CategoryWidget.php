<?php

namespace App\Admin\Widget;

use App\Repository\CategoryRepository;
use Framework\Renderer\RendererInterface;

class CategoryWidget extends AbstractAdminWidget
{
    /** @var int $position */
    private $position = 1;

    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var CategoryRepository $categoryRepository */
    private $categoryRepository;

    public function __construct(
        RendererInterface $renderer,
        CategoryRepository $categoryRepository
    ) {
        $this->renderer = $renderer;
        $this->categoryRepository = $categoryRepository;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function render(): string
    {
        $count = $this->categoryRepository->count();
        return $this->renderer->render(
            'admin/widget/category_widget.html.twig',
            [
                'count' => $count,
            ]
        );
    }
}
