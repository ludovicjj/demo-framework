<?php

namespace Framework\Renderer;

interface RendererInterface
{
    /**
     * Ajoute un chemin vers les vues
     *
     * @param string $path
     * @param string $namespace
     */
    public function addPath(string $path, string $namespace): void;

    /**
     * Retoune une vue
     *
     * @param string $template
     * @param array $parameters
     * @return string
     */
    public function render(string $template, array $parameters = []): string;

    /**
     * Ajoute des variables globales pour toutes les vues
     *
     * @param string $key
     * @param mixed $value
     */
    public function addGlobal(string $key, $value): void;
}
