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
     * @param string $name
     */
    public function get(string $uri, $callable, string $name)
    {
        $this->router->addRoute(new MezzioRoute($uri, new CallableMiddleware($callable), ['GET'], $name));
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
     * @return string|null
     */
    public function generateUri(string $name, array $parameters = []): ?string
    {
        try {
            $uri = $this->router->generateUri($name, $parameters);
            return $uri;
        } catch (\Exception $exception) {
            return null;
        }
    }
}
