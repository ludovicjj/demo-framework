<?php

namespace Framework\Router;

use Framework\Middleware\CallableMiddleware;
use Mezzio\Router\FastRouteRouter;
use Psr\Http\Message\ServerRequestInterface;
use Mezzio\Router\Route as MezzioRoute;

/**
 * Class Router
 * Enregistre une route et vérifie la correspondance entre la requete et les routes enregister.
 */
class Router
{
    /** @var FastRouteRouter */
    private $router;

    public function __construct()
    {
        $this->router = new FastRouteRouter();
    }

    /**
     * Enregistre une route avec la method GET.
     *
     * @param string $uri
     * @param callable|string|array $callable
     * @param string|null $name
     */
    public function get(string $uri, $callable, ?string $name = null)
    {
        $this->router->addRoute(new MezzioRoute($uri, new CallableMiddleware($callable), ['GET'], $name));
    }

    /**
     * Enregistre une route avec la method POST.
     *
     * @param string $uri
     * @param callable|string|array $callable
     * @param string|null $name
     */
    public function post(string $uri, $callable, ?string $name = null)
    {
        $this->router->addRoute(new MezzioRoute($uri, new CallableMiddleware($callable), ['POST'], $name));
    }

    /**
     * Enregistre une route avec la method DELETE.
     *
     * @param string $uri
     * @param callable|string|array $callable
     * @param string|null $name
     */
    public function delete(string $uri, $callable, ?string $name = null)
    {
        $this->router->addRoute(new MezzioRoute($uri, new CallableMiddleware($callable), ['DELETE'], $name));
    }



    public function crud(string $prefixUri, string $class, string $prefixName)
    {
        $this->get($prefixUri, [$class, 'index'], "$prefixName.index");

        $this->get("$prefixUri/{id:[0-9]+}", [$class, 'edit'], "$prefixName.edit");
        $this->post("$prefixUri/{id:[0-9]+}", [$class, 'edit']);

        $this->get("$prefixUri/new", [$class, 'create'], "$prefixName.create");
        $this->post("$prefixUri/new", [$class, 'create']);

        $this->delete("$prefixUri/{id:[0-9]+}", [$class, 'delete'], "$prefixName.delete");
    }

    /**
     * Vérifie la correspondance entre la request et les routes enregister dans le router.
     * Retourne une Route ou null.
     *
     * @param ServerRequestInterface $request
     * @return Route|null
     */
    public function match(ServerRequestInterface $request): ?Route
    {
        $routeResult = $this->router->match($request);

        if ($routeResult->isSuccess()) {
            /** @var CallableMiddleware $middleware */
            $middleware = $routeResult->getMatchedRoute()->getMiddleware();

            return new Route(
                $routeResult->getMatchedRouteName(),
                $middleware->getCallback(),
                $routeResult->getMatchedParams()
            );
        }
        return null;
    }

    /**
     * Retourne L'URI associé au nom de la route demandé.
     * Si le nom de la route est inconnu ou que les parametres ne correspondent pas à la regex,
     * Return null.
     *
     * @param string $name
     * @param array $parameters
     * @param array $queryParams
     * @return string|null
     */
    public function generateUri(string $name, array $parameters = [], array $queryParams = []): ?string
    {
        try {
            $uri = $this->router->generateUri($name, $parameters);
            if (!empty($queryParams)) {
                return $uri . '?' . http_build_query($queryParams);
            }
            return $uri;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
