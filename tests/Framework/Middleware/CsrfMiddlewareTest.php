<?php

namespace Tests\Framework\Middleware;

use Framework\Exceptions\CsrfInvalidException;
use Framework\Middleware\CsrfMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CsrfMiddlewareTest extends TestCase
{
    /** @var CsrfMiddleware $middleware */
    private $middleware;

    /** @var array $session */
    private $session;

    public function setUp(): void
    {
        $this->session = [];
        $this->middleware = new CsrfMiddleware($this->session);
    }

    public function testLetPassGetRequest()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $request = new ServerRequest('GET', '/demo');

        $handler->expects($this->once())
            ->method('handle')
            ->will($this->returnValue(new Response()));


        $response = $this->middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testBlockPostRequestWithoutToken()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->onlyMethods(['handle'])
            ->getMock();

        $handler->expects($this->never())
            ->method('handle');

        $request = new ServerRequest('POST', '/demo');
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $handler);
    }

    public function testBlockPostRequestWitInvalidToken()
    {
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)
            ->onlyMethods(['handle'])
            ->getMock();

        $handler->expects($this->never())
            ->method('handle');

        $request = new ServerRequest('POST', '/demo');
        $request = $request->withParsedBody(['_csrf' => 'badToken']);
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $handler);
    }

    public function testLetPassPostRequestWithValidToken()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $token = $this->middleware->generateToken();
        $request = new ServerRequest('POST', '/demo');
        $request = $request->withParsedBody(['_csrf' => $token]);

        $handler->expects($this->once())
            ->method('handle')
            ->willReturn(new Response());

        $response = $this->middleware->process($request, $handler);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    public function testLetPassPostRequestWithValidTokenOnce()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $token = $this->middleware->generateToken();
        $request = new ServerRequest('POST', '/demo');
        $request = $request->withParsedBody(['_csrf' => $token]);


        $handler->expects($this->once())
            ->method('handle');

        $this->middleware->process($request, $handler);
        $this->expectException(CsrfInvalidException::class);
        $this->middleware->process($request, $handler);
    }

    public function testLimitToken()
    {
        for ($i = 0; $i < 100; $i++) {
            $token = $this->middleware->generateToken();
        }

        $this->assertCount(50, $this->middleware->getSession()['csrf']);
        $this->assertEquals($token, $this->middleware->getSession()['csrf'][49]);
    }
}