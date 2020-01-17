<?php

namespace Tests;

use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class AppTest extends TestCase
{
    public function testRedirect()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/blog/');
        $response = $app->run($request);
        $this->assertContains('/blog', $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testValidUri()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/blog');
        $response = $app->run($request);
        $this->assertStringContainsString(
            '<h1>Bienvenue sur le Blog</h1>',
            $response->getBody()->__toString()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidUri()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/azeaze');
        $response = $app->run($request);
        $this->assertStringContainsString(
            '<h1>Erreur 404</h1>',
            $response->getBody()->__toString()
        );
        $this->assertEquals(404, $response->getStatusCode());
    }
}