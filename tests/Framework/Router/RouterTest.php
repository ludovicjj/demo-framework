<?php

namespace Tests\Framework\Router;

use Framework\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RouterTest extends TestCase
{
    /** @var Router */
    private $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testRouterWithValidUri(): void
    {
        $request = new ServerRequest('GET', '/blog');
        $this->router->get(
            '/blog',
            function (ServerRequestInterface $request) { return 'demo'; },
            'blog'
        );
        $route = $this->router->match($request);

        $this->assertEquals('blog', $route->getName());
        $this->assertEquals('demo', call_user_func_array($route->getCallback(), [$request]));
    }

    public function testRouterWithInvalidUri(): void
    {
        $request = new ServerRequest('GET', '/demo');
        $this->router->get(
            '/blog',
            function (ServerRequestInterface $request) { return 'demo'; },
            'blog'
        );
        $route = $this->router->match($request);
        $this->assertNull($route);
    }

    public function testRouterWithParameters(): void
    {
        $request = new ServerRequest('GET', '/blog/slug-post-4');
        $this->router->get(
            '/blog/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            function (ServerRequestInterface $request) { return 'demo'; },
            'blog.show'
        );
        $route = $this->router->match($request);

        $this->assertEquals('blog.show', $route->getName());
        $this->assertEquals('demo', call_user_func_array($route->getCallback(), [$request]));
        $this->assertEquals(['slug' => 'slug-post', 'id' => 4], $route->getParameters());
    }

    public function testMethodAddRoute(): void
    {
        $requestWithMethodGet = new ServerRequest('GET', '/blog/slug-post-4');
        $requestWithMethodPost = new ServerRequest('POST', '/blog/slug-post-4');

        $this->router->addRoute(
            '/blog/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            function (ServerRequestInterface $request) { return 'demo'; },
            ['GET', 'POST'],
            'blog.show'
        );
        $routeToMethodGet = $this->router->match($requestWithMethodGet);
        $routeToMethodPost = $this->router->match($requestWithMethodPost);

        $this->assertEquals('blog.show', $routeToMethodGet->getName());
        $this->assertEquals(['slug' => 'slug-post', 'id' => 4], $routeToMethodGet->getParameters());
        $this->assertEquals(
            'demo',
            call_user_func_array($routeToMethodGet->getCallback(), [$requestWithMethodGet])
        );

        $this->assertEquals('blog.show', $routeToMethodPost->getName());
        $this->assertEquals(['slug' => 'slug-post', 'id' => 4], $routeToMethodPost->getParameters());
        $this->assertEquals(
            'demo',
            call_user_func_array($routeToMethodPost->getCallback(), [$requestWithMethodPost])
        );
    }

    public function testGenerateUri(): void
    {
        $this->router->get(
            '/blog/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            function (ServerRequestInterface $request) { return 'demo'; },
            'blog.show'
        );
        $uri = $this->router->generateUri('blog.show', ['slug' => 'test-slug', 'id' => 4]);

        $this->assertEquals('/blog/test-slug-4', $uri);
    }

    public function testGenerateUriWithQueryParams(): void
    {
        $this->router->get(
            '/blog/{slug:[a-z0-9\-]+}-{id:[0-9]+}',
            function (ServerRequestInterface $request) { return 'demo'; },
            'blog.show'
        );
        $uri = $this->router->generateUri(
            'blog.show',
            ['slug' => 'test-slug', 'id' => 4],
            ['page' => 5, 'date' => 'test']
        );

        $this->assertEquals('/blog/test-slug-4?page=5&date=test', $uri);
    }
}
