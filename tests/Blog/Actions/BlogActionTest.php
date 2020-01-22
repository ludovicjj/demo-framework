<?php

namespace Tests\Blog\Actions;

use App\Blog\Actions\BlogAction;
use App\Blog\Repository\PostRepository;
use App\Framework\Exceptions\NotFoundException;
use App\Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class BlogActionTest extends TestCase
{
    /** @var BlogAction */
    private $action;

    /** @var MockObject */
    private $renderer;

    /** @var MockObject */
    private $postRepository;

    /** @var MockObject */
    private $router;

    public function setUp(): void
    {
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->postRepository = $this->createMock(PostRepository::class);
        $this->router = $this->createMock(Router::class);

        $this->action = new BlogAction(
            $this->renderer,
            $this->router,
            $this->postRepository
        );
    }

    public function makePost(int $id, string $slug): \stdClass
    {
        $post =  new \stdClass();
        $post->id = $id;
        $post->slug = $slug;
        return $post;
    }

    public function testShowRedirect()
    {
        $post = $this->makePost(9, 'mon-super-slug-test');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->id)
            ->withAttribute('slug', 'slug-test')
        ;

        $this->postRepository->method('find')->willReturn($post);
        $this->router->method('generateUri')->willReturn('blog/demo-test-9');

        $response = call_user_func_array([$this->action, 'show'], [$request]);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['blog/demo-test-9'], $response->getHeader('Location'));
    }

    public function testShowException()
    {
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', 236)
            ->withAttribute('slug', 'slug-test')
        ;

        $this->postRepository->method('find')->willReturn(false);
        $this->expectException(NotFoundException::class);
        call_user_func_array([$this->action, 'show'], [$request]);
    }

    public function testShowRender()
    {
        $post = $this->makePost(9, 'mon-super-slug-test');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->id)
            ->withAttribute('slug', $post->slug)
        ;

        $this->postRepository->method('find')->willReturn($post);
        $this->renderer->method('render')->willReturn("<h1>$post->slug</h1>");

        $response = call_user_func_array([$this->action, 'show'], [$request]);
        $this->assertEquals('<h1>mon-super-slug-test</h1>', $response);
    }
}
