<?php

namespace Framework\Middleware;

use Framework\Router\Route;
use GuzzleHttp\Psr7\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

class DispatcherMiddleware implements MiddlewareInterface
{
    /** @var ContainerInterface $container */
    private $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var Route|null $route */
        $route = $request->getAttribute(Route::class);

        //TODO case : route match
        if (!is_null($route)) {
            $response = $this->initCallback($route->getCallback(), $request);

            //TODO case : Vérifie le type de la response
            if (is_string($response)) {
                return new Response(200, [], $response);
            } elseif ($response instanceof ResponseInterface) {
                return $response;
            } else {
                throw new Exception('Response must be string or instance of ResponseInterface');
            }
        }
        //TODO case : route doesn't match, go to next middleware (NotFoundMiddleware)
        return $handler->handle($request);
    }

    /**
     * @param mixed $callback
     * @param ServerRequestInterface $request
     * @return string|ResponseInterface
     * @throws Exception
     */
    private function initCallback($callback, ServerRequestInterface $request)
    {
        //TODO callback is string, init class and call method __invoke with request
        if (is_string($callback)) {
            return call_user_func_array($this->container->get($callback), [$request]);
        }

        //TODO callback is array, init class and call named method with request
        if (is_array($callback) && is_string($callback[0])) {
            $object = $this->container->get($callback[0]);
            $method = $callback[1];
            if (method_exists($object, $method)) {
                return call_user_func_array([$object, $method], [$request]);
            } else {
                throw new Exception(
                    sprintf('Method %s() doesn\'t exist in class name : %s', $method, get_class($object))
                );
            }
        }

        //TODO callback is function, call function with request
        return call_user_func_array($callback, [$request]);
    }
}
