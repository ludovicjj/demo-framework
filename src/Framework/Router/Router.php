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

    /**
     * Router constructor.
     * @param string|null $cache
     */
    public function __construct(?string $cache = null)
    {
        $this->router = new FastRouteRouter(
            null,
            null,
            [
                FastRouteRouter::CONFIG_CACHE_ENABLED => !is_null($cache),
                FastRouteRouter::CONFIG_CACHE_FILE => $cache,
            ]
        );
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

    /**
     * Enregistre une route avec une ou plusieur methods
     *
     * @param array $method
     * @param string $uri
     * @param callable|string|array $callable
     * @param string|null $name
     */
    public function addRoute(string $uri, $callable, array $method, ?string $name = null)
    {
        $this->router->addRoute(new MezzioRoute($uri, new CallableMiddleware($callable), $method, $name));
    }

    /**
     * Method spécifique à l'app.
     * Enregistre toutes les routes pour le CRUD des posts et des catégories.
     * Favorisé les method générique : post(), get(), delete() ou addRoute()
     *
     * @param string $prefixUri
     * @param string $className
     * @param string $prefixName
     */
    public function crud(string $prefixUri, string $className, string $prefixName)
    {
        $this->get($prefixUri, [$className, 'index'], "$prefixName.index");

        $this->addRoute("$prefixUri/{id:[0-9]+}", [$className, 'edit'], ['GET', 'POST'], "$prefixName.edit");

        $this->get("$prefixUri/new", [$className, 'create'], "$prefixName.create");
        $this->post("$prefixUri/new", [$className, 'create']);

        $this->delete("$prefixUri/{id:[0-9]+}", [$className, 'delete'], "$prefixName.delete");
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
