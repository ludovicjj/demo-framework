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
            new TwigFunction('path', [$this, 'path']),
            new TwigFunction('is_subpath', [$this, 'isSubPath']),
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

    /**
     * Detecte si la l'uri current est un enfant de l'uri parent
     *
     * @param string $routeName
     * @return bool
     */
    public function isSubPath(string $routeName): bool
    {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '/';
        $expectedUri = $this->router->generateUri($routeName);

        if (strpos($currentUri, $expectedUri, 0) !== false) {
            return true;
        }
        return false;
    }
}
