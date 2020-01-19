<?php

namespace App\Framework\Renderer;

class PHPRenderer
{
    const DEFAULT_NAMESPACE = 'MAIN__';

    /** @var array */
    private $paths = [];

    /** @var array */
    private $globals = [];

    /**
     * Ajoute un chemin vers les vues
     *
     * @param string $path
     * @param string $namespace
     */
    public function addPath(string $path, string $namespace = self::DEFAULT_NAMESPACE): void
    {
        if ($namespace === self::DEFAULT_NAMESPACE) {
            $this->paths[self::DEFAULT_NAMESPACE] = $path;
        }
        $this->paths[$namespace] = $path;
    }

    /**
     * Retoune une vue
     *
     * @param string $template
     * @param array $parameters
     * @return string
     */
    public function render(string $template, array $parameters = []): string
    {
        if ($this->hasNamespace($template)) {
            $path = $this->replaceNamespace($template);
        } else {
            $path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . $template;
        }
        ob_start();
        $renderer = $this;
        extract($this->globals);
        extract($parameters);
        require($path);
        return ob_get_clean();
    }

    /**
     * Ajoute des variables globales pour toutes les vues
     *
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void
    {
        $this->globals[$key] = $value;
    }

    private function hasNamespace(string $template): bool
    {
        return $template[0] === '@';
    }

    private function getNamespace(string $template): string
    {
        return substr($template, 1, strpos($template, '/') - 1);
    }

    private function replaceNamespace(string $template): string
    {
        $namespace = $this->getNamespace($template);
        return str_replace('@'.$namespace, $this->paths[$namespace], $template);
    }
}
