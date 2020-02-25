<?php

namespace Framework\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodMiddleware implements MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        //TODO update request method if key "_method" exist
        $parseBody = $request->getParsedBody();
        if (array_key_exists('_method', $parseBody) &&
            in_array($parseBody['_method'], ['DELETE', 'PUT'])
        ) {
            $request = $request->withMethod($parseBody['_method']);
        }

        return $handler->handle($request);
    }
}
