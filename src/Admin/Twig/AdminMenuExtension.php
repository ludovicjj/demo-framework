<?php

namespace App\Admin\Twig;

use App\Admin\Widget\AdminWidgetInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AdminMenuExtension extends AbstractExtension
{
    private $widgets;

    /**
     * @param AdminWidgetInterface[] $widgets
     */
    public function __construct(
        array $widgets
    ) {
        $this->widgets = $widgets;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_menu', [$this, 'renderMenu'], ['is_safe' => ['html']])
        ];
    }

    public function renderMenu()
    {
        return array_reduce($this->widgets, function (string $carry, AdminWidgetInterface $widgets) {
            return $carry . $widgets->renderMenu();
        }, '');
    }
}
