<?php

namespace App\Framework\Renderer;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigRenderer implements RendererInterface
{
    const DEFAULT_NAMESPACE = 'MAIN__';

    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param string $path
     * @param string $namespace
     * @throws \Twig\Error\LoaderError
     */
    public function addPath(string $path, string $namespace = self::DEFAULT_NAMESPACE): void
    {
        /** @var FilesystemLoader $loader */
        $loader = $this->twig->getLoader();

        if ($namespace === self::DEFAULT_NAMESPACE) {
            $loader->addPath($path);
        } else {
            $loader->addPath($path, $namespace);
        }
    }

    /**
     * @param string $template
     * @param array $parameters
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $template, array $parameters = []): string
    {
        return $this->twig->render($template, $parameters);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->twig->addGlobal($key, $value);
    }
}
