<?php

namespace App\Admin\Widget;

use App\Repository\PostRepository;
use Framework\Renderer\RendererInterface;

class PostWidget extends AbstractAdminWidget
{
    private $position = 2;

    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var PostRepository $postRepository */
    private $postRepository;

    public function __construct(
        RendererInterface $renderer,
        PostRepository $postRepository
    ) {
        $this->renderer = $renderer;
        $this->postRepository = $postRepository;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function render(): string
    {
        $count = $this->postRepository->count();
        return $this->renderer->render(
            'admin/widget/post_widget.html.twig',
            [
                'count' => $count,
            ]
        );
    }
}
