<?php

namespace Tests\Framework\Middleware;

use Framework\Middleware\MethodMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MethodMiddlewareTest extends TestCase
{
    /** @var MethodMiddleware $middleware */
    private $middleware;

    public function setUp(): void
    {
        $this->middleware = new MethodMiddleware();
    }

    public function testUpdateMethod()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->onlyMethods(['handle'])
            ->getMock();

        $handler->expects($this->once())
            ->method('handle')
            ->with($this->callback(function(ServerRequestInterface $request) {
                return $request->getMethod() === 'DELETE';
            }));

        $request = (new ServerRequest('POST', 'demo'))
            ->withParsedBody([
                '_method' => 'DELETE'
            ]);

        $this->middleware->process($request, $handler);
    }
}