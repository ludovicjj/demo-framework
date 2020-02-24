<?php

namespace App\Admin\Widget;

use Framework\Renderer\RendererInterface;

class MenuWidget extends AbstractAdminWidget
{
    /** @var RendererInterface $renderer */
    private $renderer;

    public function __construct(
        RendererInterface $renderer
    ) {
        $this->renderer = $renderer;
    }

    public function renderMenu(): string
    {
        return $this->renderer->render('admin/widget/menu_widget.html.twig');
    }
}
