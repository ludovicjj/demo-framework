<?php

namespace App\Admin\Actions;

use App\Admin\Widget\AdminWidgetInterface;
use Framework\Renderer\RendererInterface;

class DashboardAction
{
    /** @var RendererInterface $renderer */
    private $renderer;

    /** @var AdminWidgetInterface[] */
    private $widgets;

    public function __construct(
        RendererInterface $renderer,
        array $widgets
    ) {
        $this->renderer = $renderer;
        $this->widgets = $widgets;
    }

    public function dashboard()
    {
        //TODO : Réorganise le tableau d'objet en fonction de leur position
        usort($this->widgets, function (AdminWidgetInterface $a, AdminWidgetInterface $b) {
            return strcmp($a->getPosition(), $b->getPosition());
        });

        $widgets = array_reduce($this->widgets, function (string $carry, AdminWidgetInterface $widgets) {
            return $carry . $widgets->render();
        }, '');

        return $this->renderer->render(
            'admin/dashboard/dashboard.html.twig',
            [
                'widgets' => $widgets,
            ]
        );
    }
}
