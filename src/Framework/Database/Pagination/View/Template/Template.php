<?php

namespace Framework\Database\Pagination\View\Template;

abstract class Template
{
    protected static $defaultOptions = array();

    private $routeGenerator;
    private $options;

    public function __construct()
    {
        $this->initializeOptions();
    }

    /**
     * Définit le routeGenerator avec le result du callback
     *
     * @param $routeGenerator
     */
    public function setRouteGenerator($routeGenerator)
    {
        $this->routeGenerator = $routeGenerator;
    }

    /**
     * Fusionne le tableau d'option de la Template avec celui transmit à la vue
     *
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Recupere le tableau d'options de la classe instancié
     */
    private function initializeOptions()
    {
        $this->options = static::$defaultOptions;
    }

    protected function generateRoute($page)
    {
        return call_user_func($this->getRouteGenerator(), $page);
    }

    private function getRouteGenerator()
    {
        if (!$this->routeGenerator) {
            throw new \RuntimeException('There is no route generator.');
        }

        return $this->routeGenerator;
    }

    protected function option($name)
    {
        if (!isset($this->options[$name])) {
            throw new \InvalidArgumentException(sprintf('The option "%s" does not exist.', $name));
        }

        return $this->options[$name];
    }
}
