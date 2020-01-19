<?php

namespace Framework\Router;

/**
 * Class Route
 * Represente la route qui correspond à la requete
 */
class Route
{
    /** @var string */
    private $name;

    /** @var string|callable|array */
    private $callback;

    /** @var array */
    private $parameters;

    public function __construct(
        string $name,
        $callback,
        array $parameters
    ) {

        $this->name = $name;
        $this->callback = $callback;
        $this->parameters = $parameters;
    }

    /**
     * Recupere le nom de la route
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Recupere le callback de la route
     *
     * @return callable|string|array
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Recupere les parametres de la route
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
