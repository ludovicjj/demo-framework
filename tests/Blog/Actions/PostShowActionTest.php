<?php

namespace Tests\Blog\Actions;

use App\Blog\Actions\PostShowAction;
use App\Entity\Post;
use App\Repository\PostRepository;
use Framework\Exceptions\NotFoundException;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PostShowActionTest extends TestCase
{
    /** @var PostShowAction $action */
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

        $this->action = new PostShowAction(
            $this->postRepository,
            $this->renderer,
            $this->router
        );
    }

    public function makePost(int $id, string $slug): Post
    {
        $post =  new Post();
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

        $this->postRepository
            ->expects($this->once())
            ->method('findWithCategory')
            ->with(['id' => $request->getAttribute('id')])
            ->willReturn($post);

        $this->router
            ->expects($this->once())
            ->method('generateUri')
            ->with('blog.show', ['slug' => $post->slug, 'id' => $post->id])
            ->willReturn('blog/mon-super-slug-test-9');

        $response = call_user_func_array([$this->action, 'show'], [$request]);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['blog/mon-super-slug-test-9'], $response->getHeader('Location'));
    }

    public function testShowException()
    {
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', 236)
            ->withAttribute('slug', 'slug-test')
        ;

        $this->expectException(NotFoundException::class);

        $this->postRepository
            ->expects($this->once())
            ->method('findWithCategory')
            ->with(['id' => $request->getAttribute('id')])
            ->will($this->throwException(new NotFoundException()));
        call_user_func_array([$this->action, 'show'], [$request]);
    }

    public function testShowRender()
    {
        $post = $this->makePost(9, 'mon-super-slug-test');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->id)
            ->withAttribute('slug', $post->slug)
        ;

        $this->postRepository
            ->expects($this->once())
            ->method('findWithCategory')
            ->with(['id' => $request->getAttribute('id')])
            ->willReturn($post);

        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->willReturn("<h1>$post->slug</h1>");

        $response = call_user_func_array([$this->action, 'show'], [$request]);

        $this->assertEquals('<h1>mon-super-slug-test</h1>', $response);
    }
}
