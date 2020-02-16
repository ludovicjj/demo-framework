<?php

namespace Tests\Admin\Actions;

use App\Admin\Actions\PostCrudAction;
use App\Entity\Post;
use App\Repository\CategoryRepository;
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

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var PostCrudAction */
    private $postCrudAction;

    public function setUp(): void
    {
        $pdo = $this->getPdo();
        $this->migrate($pdo);
        $this->postRepository = new PostRepository($pdo);
        $this->renderer = $this->createMock(RendererInterface::class);
        $this->router = $this->createMock(Router::class);
        $this->flash = $this->createMock(FlashService::class);
        $this->categoryRepository = new CategoryRepository($pdo);
        $this->postCrudAction = new PostCrudAction(
            $this->renderer,
            $this->postRepository,
            $this->router,
            $this->flash,
            $this->categoryRepository
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
        // Lance fixtures
        $this->seed($this->postRepository->getPdo());

        $data = [
            'name' => 'hey',
            'slug' => 'demo',
            'content' => 'demo content',
            'created_at' => '2020-02-02 13:46:00',
            'category_id' => '1'
        ];

        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody($data);

        $formData = self::callPrivateMethod($this->postCrudAction, 'getFilterParseBody', [$request]);
        $validator = self::callPrivateMethod($this->postCrudAction, 'getValidator', [$request]);
        $item = self::callPrivateMethod($this->postCrudAction, 'hydrateFormWithCurrentData', [$formData, null]);
        $errors = $validator->getErrors();

        $this->assertCount(6, $formData);
        $this->assertInstanceOf(Validator::class, $validator);
        $this->assertCount(2, $errors);


        foreach ($errors as $error) {
            $this->assertInstanceOf(ValidationError::class, $error);
            $this->assertContains($error->getProperty(), ['name', 'slug']);
        }

        $params = [
            'errors' => $errors,
            'item' => $item,
        ];

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/posts/create.html.twig',
                self::callPrivateMethod($this->postCrudAction, 'sendParamsToView', [$params])
            )->willReturn('form is not valid');
        $this->assertEquals('form is not valid', $this->postCrudAction->create($request));
    }

    public function testCreateWithMethodPostAndValidForm(): void
    {
        // Lance fixtures
        $this->seed($this->postRepository->getPdo());

        $request = (new ServerRequest('POST', '/'))
            ->withParsedBody(
                [
                    'name' => 'Léviathan',
                    'slug' => 'alt-236',
                    'content' => 'demo content',
                    'created_at' => '2020-02-01 18:17:00',
                    'category_id' => '1'
                ]
            );
        $this->router->expects($this->once())
            ->method('generateUri')
            ->with('admin.posts.index')
            ->willReturn('/admin/posts');

        $this->renderer->expects($this->never())->method('render');

        $response = $this->postCrudAction->create($request);
        $postId = $this->postRepository->getPdo()->lastInsertId();
        $post = $this->postRepository->find($postId);

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
        $params = [
            'errors' => null,
            'item' => self::callPrivateMethod(
                $this->postCrudAction,
                'getNewEntity',
                [$this->postRepository->getEntity()]
            )
        ];

        $this->renderer->expects($this->once())
            ->method('render')
            ->with(
                'admin/posts/create.html.twig',
                self::callPrivateMethod($this->postCrudAction, 'sendParamsToView', [$params])
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
