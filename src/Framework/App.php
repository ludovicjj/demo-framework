<?php

namespace Framework;

use Framework\Exceptions\NotFoundException;
use Framework\Router\Router;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    /** @var array */
    private $modules = [];

    /** @var ContainerInterface */
    private $container;

    /**
     * App constructor.
     * @param ContainerInterface $container
     * @param array $modules
     */
    public function __construct(
        ContainerInterface $container,
        array $modules = []
    ) {
        $this->container = $container;
        foreach ($modules as $module) {
            $this->modules[] = $container->get($module);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();

        //TODO update request method if key "_method" exist
        $parseBody = $request->getParsedBody();
        if (array_key_exists('_method', $parseBody) &&
            in_array($parseBody['_method'], ['DELETE', 'PUT'])
        ) {
            $request = $request->withMethod($parseBody['_method']);
        }

        //TODO case : trailingSlash
        if (!empty($uri) && $uri !== '/' && $uri[-1] === '/') {
            return (new Response())
                ->withStatus(301)
                ->withHeader('Location', substr($uri, 0, -1))
            ;
        }

        //TODO recuperation du router via le container
        $router = $this->container->get(Router::class);
        $route = $router->match($request);

        //TODO case : request doesn't match, return Response 404
        if (\is_null($route)) {
            return new Response(404, [], '<h1>Erreur 404</h1>');
        }

        //TODO case : hydrate la requete avec les parametres de la route
        $parameters = $route->getParameters();
        $request = array_reduce(
            array_keys($parameters),
            function (ServerRequestInterface $request, $key) use ($parameters) {
                return $request->withAttribute($key, $parameters[$key]);
            },
            $request
        );

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
            throw new \Exception('Response must be string or instance of ResponseInterface');
        }
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param mixed $callback
     * @param ServerRequestInterface $request
     * @return string|ResponseInterface
     * @throws \Exception
     */
    private function initCallback($callback, ServerRequestInterface $request)
    {
        if (\is_string($callback)) {
            return call_user_func_array($this->container->get($callback), [$request]);
        }

        if (\is_array($callback) && \is_string($callback[0])) {
            $object = $this->container->get($callback[0]);
            $method = $callback[1];
            if (method_exists($object, $method)) {
                return call_user_func_array([$object, $method], [$request]);
            } else {
                throw new \Exception(
                    sprintf(
                        'Method %s() doesn\'t exist in class name : %s',
                        $method,
                        get_class($object)
                    )
                );
            }
        }
        return call_user_func_array($callback, [$request]);
    }
}
