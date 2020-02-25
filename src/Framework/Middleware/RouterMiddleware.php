<?php

namespace Framework\Middleware;

use Framework\Router\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouterMiddleware implements MiddlewareInterface
{
    /** @var Router $router */
    private $router;

    public function __construct(
        Router $router
    ) {
        $this->router = $router;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->match($request);

        //TODO case : request doesn't match, go to next middleware
        if (is_null($route)) {
            return $handler->handle($request);
        }

        //TODO case : hydrate request with parameters from route
        $parameters = $route->getParameters();

        /** @var ServerRequestInterface $request */
        $request = array_reduce(
            array_keys($parameters),
            function (ServerRequestInterface $request, $key) use ($parameters) {
                return $request->withAttribute($key, $parameters[$key]);
            },
            $request
        );

        $request = $request->withAttribute(get_class($route), $route);

        return $handler->handle($request);
    }
}
