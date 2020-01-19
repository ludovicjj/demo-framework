<?php

namespace Framework;

use Framework\Router\Router;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    /** @var array */
    private $modules = [];

    /** @var Router */
    private $router;

    /**
     * App constructor.
     * @param string[] $modules
     */
    public function __construct(array $modules = [])
    {
        $this->router = new Router();
        foreach ($modules as $module) {
            $this->modules[] = new $module($this->router);
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

        //TODO case : trailingSlash
        if (!empty($uri) && $uri !== '/' && $uri[-1] === '/') {
            $response = (new Response())
                ->withStatus(301)
                ->withHeader('Location', substr($uri, 0, -1))
            ;
            return $response;
        }

        $route = $this->router->match($request);

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

        $response = call_user_func_array($route->getCallback(), [$request]);

        //TODO case : Vérifie le type de la response
        if (is_string($response)) {
            return new Response(200, [], $response);
        } elseif ($response instanceof ResponseInterface) {
            return $response;
        } else {
            throw new \Exception('Response must be string or instance of ResponseInterface');
        }
    }
}
