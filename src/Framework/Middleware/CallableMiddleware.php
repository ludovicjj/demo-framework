<?php

namespace Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CallableMiddleware implements MiddlewareInterface
{
    /**
     * @var callable|string|array $callback
     */
    private $callback;

    /**
     * CallableMiddleware constructor.
     * @param callable|string|array $callback
     */
    public function __construct($callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return callable|string|array
     */
    public function getCallback()
    {
        return $this->callback;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $handler->handle($request);
    }
}
