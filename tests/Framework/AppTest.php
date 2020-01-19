<?php

namespace Tests\Framework;

use App\Blog\BlogModule;
use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Framework\Module\ErrorModule;

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

    public function testBlogModuleMethodIndex()
    {
        $app = new App([
            BlogModule::class
        ]);
        $request = new ServerRequest('GET', '/blog');
        $response = $app->run($request);

        $this->assertStringContainsString(
            '<h1>Bienvenue sur l\'accueil du Blog</h1>',
            $response->getBody()->__toString()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testBlogModuleMethodShow()
    {
        $app = new App([
            BlogModule::class
        ]);
        $request = new ServerRequest('GET', '/blog/test-slug');
        $response = $app->run($request);
        $this->assertStringContainsString(
            '<h1>Bienvenue sur le post test-slug</h1>',
            $response->getBody()->__toString()
        );
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testErrorModule()
    {
        $app = new App([
            ErrorModule::class
        ]);
        $request = new ServerRequest('GET', '/demo');
        $this->expectException(\Exception::class);
        $app->run($request);
    }

    public function testInvalidUri()
    {
        $app = new App();
        $request = new ServerRequest('GET', '/azeaze');
        $response = $app->run($request);
        $this->assertStringContainsString('<h1>Erreur 404</h1>', $response->getBody()->__toString());
        $this->assertEquals(404, $response->getStatusCode());
    }
}
