<?php

namespace Framework;

use Framework\Exceptions\NotFoundException;
use Framework\Router\Router;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use DI\ContainerBuilder;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function is_string;
use function is_null;
use function is_array;
use Exception;

class App implements RequestHandlerInterface
{
    /**
     * Contient la liste des modules
     *
     * @var string[] $modules
     */
    private $modules = [];

    /**
     * @var ContainerInterface|null $container
     */
    private $container;

    /**
     * Contient la definition principale pour PHPDI
     *
     * @var string $definition
     */
    private $definition;

    /**
     * @var string[] $middlewares
     */
    private $middlewares = [];

    private $index = 0;

    public function __construct(
        string $definition
    ) {
        $this->definition = $definition;
    }

    /**
     * Ajoute un module à app
     *
     * @param string $module
     * @return $this
     */
    public function addModule(string $module): self
    {
        $this->modules[] = $module;

        return $this;
    }

    /**
     * Ajoute un middleware à app
     *
     * @param string $middleware
     * @return $this
     */
    public function pipe(string $middleware)
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->getMiddleware();
        if (!is_null($middleware) && $middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, $this);
        } else {
            throw new Exception('Middleware unable to handle this request');
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Exception
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        //TODO initialize les different modules
        foreach ($this->modules as $module) {
            $this->getContainer()->get($module);
        }
        return $this->handle($request);

        /*
        //TODO initialise la class et lance la method.
        try {
            $response = $this->initCallback($route->getCallback(), $request);
        } catch (NotFoundException $notFoundException) {
            return new Response(
                404,
                [],
                '<h1>Erreur 404</h1><p>'. $notFoundException->getMessage() .'</p>'
            );
        }

        //TODO case : Vérifie le type de la response
        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new Exception('Response must be string or instance of ResponseInterface');
        }
        */
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            $builder = new ContainerBuilder();
            $builder->addDefinitions($this->definition);

            /** @var string $module */
            foreach ($this->modules as $module) {
                if (!is_null(constant($module.'::DEFINITIONS'))) {
                    $builder->addDefinitions(constant($module.'::DEFINITIONS'));
                }
            }

            try {
                $this->container = $builder->build();
            } catch (Exception $e) {
                $e->getMessage();
            }
            return $this->container;
        }

        return $this->container;
    }

    /**
     * @return MiddlewareInterface|null
     */
    private function getMiddleware(): ?MiddlewareInterface
    {
        if (array_key_exists($this->index, $this->middlewares)) {
            if (is_string($this->middlewares[$this->index])) {
                $middleware = $this->container->get($this->middlewares[$this->index]);
            } else {
                $middleware = $this->middlewares[$this->index];
            }
            $this->index++;
            return $middleware;
        }

        return null;
    }
}
