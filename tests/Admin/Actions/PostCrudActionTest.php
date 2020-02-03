<?php

namespace Tests\Admin\Actions;

use App\Admin\Actions\PostCrudAction;
use App\Entity\Post;
use App\Repository\PostRepository;
use Framework\Exceptions\NotFoundException;
use Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use Framework\Session\FlashService;
use Framework\Validator\ValidationError;
use Framework\Validator\Validator;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Tests\DatabaseTestCase;

class PostCrudActionTest extends DatabaseTestCase
{
    /** @var PostRepository */
    private $postRepository;

    /** @var MockObject */
    private $renderer;

    /** @var MockObject */
    private $router;

    /** @var MockObject */
    private $flash;

    /** @var PostCrudAction */
    private $postCrudAction;

    public function setUp(): void
    {
        parent::setUp();
        $this->postRepository = new PostRepository($this->getPdo());
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->router = $this->createMock(Router::class);
        $this->flash = $this->createMock(FlashService::class);
        $this->postCrudAction = new PostCrudAction(
            $this->renderer,
            $this->postRepository,
            $this->router,
            $this->flash
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testIndexException(): void
    {
        $request = (new ServerRequest('GET', '/'))
            ->withQueryParams(['page' => 100]);

        $this->expectException(NotFoundException::class);
        $this->postCrudAction->index($request);
    }

    public function testCreateWithMethodPostAndInvalidFormData(): void
    {
        $data = [
            'name' => 'hey',
            'slug' => 'demo',
            'content' => 'demo content',
            'created_at' => '2020-02-02 13:46:00'
        ];

        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody($data);

        $formData = self::callPrivateMethod($this->postCrudAction, 'getFilterParseBody', [$request]);
        $validator = self::callPrivateMethod($this->postCrudAction, 'getValidator', [$request]);
        $item = self::callPrivateMethod($this->postCrudAction, 'hydrateFormWithCurrentData', [$formData, null]);
        $errors = $validator->getErrors();

        $this->assertCount(5, $formData);
        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertCount(2, $errors);

        /** @var ValidationError $error */
        foreach ($errors as $error) {
            $this->assertInstanceOf(ValidationError::class, $error);
            $this->assertContains($error->getProperty(), ['name', 'slug']);
        }

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/posts/create.html.twig',
                [
                    'errors' => $errors,
                    'item' => $item
                ]
            )->willReturn('form is not valid');
        $this->assertEquals('form is not valid', $this->postCrudAction->create($request));
    }

    public function testCreateWithMethodPostAndValidForm(): void
    {
        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody(
                [
                    'name' => 'Léviathan',
                    'slug' => 'alt-236',
                    'content' => 'demo content',
                    'created_at' => '2020-02-01 18:17:00',
                ]
            );
        $this->router->expects($this->once())
            ->method('generateUri')
            ->with('admin.posts.index')
            ->willReturn('/admin/posts');

        $this->renderer->expects($this->never())->method('render');

        $response = $this->postCrudAction->create($request);
        $post = $this->postRepository->find(1);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/admin/posts'], $response->getHeader('Location'));
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Léviathan', $post->name);
        $this->assertEquals('2020-02-01 18:17:00', $post->created_at->format('Y-m-d H:i:s'));
    }

    public function testCreateWithMethodGet(): void
    {
        $request = new ServerRequest('GET', '/');
        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/posts/create.html.twig',
                [
                    'errors' => null,
                    'item' => self::callPrivateMethod(
                        $this->postCrudAction,
                        'getNewEntity',
                        [$this->postRepository->getEntity()]
                    )
                ]
            )->willReturn('demo');
        $this->assertEquals('demo', $this->postCrudAction->create($request));
    }


    /**
     * @param mixed $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    private static function callPrivateMethod($obj, string $name, array $args)
    {
        try {
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod($name);
            $method->setAccessible(true);
            return $method->invokeArgs($obj, $args);
        } catch (\ReflectionException $e) {
            return $e->getMessage();
        }
    }
}
