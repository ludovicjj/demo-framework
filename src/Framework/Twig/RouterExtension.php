<?php

namespace Framework\Twig;

use Framework\Router\Router;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class RouterExtension extends AbstractExtension
{
    /** @var Router */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('path', [$this, 'path'])
        ];
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return string|null
     */
    public function path(string $name, array $parameters = []): ?string
    {
        return $this->router->generateUri($name, $parameters);
    }
}
