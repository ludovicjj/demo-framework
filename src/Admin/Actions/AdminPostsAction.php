<?php

namespace App\Admin\Actions;

use App\Blog\Entity\Post;
use App\Blog\Repository\PostRepository;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router\Router;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;
use Framework\Exceptions\NotFoundException;

class AdminPostsAction
{
    /** @var RendererInterface */
    private $renderer;

    /** @var PostRepository */
    private $postRepository;

    /** @var Router */
    private $router;

    /** @var FlashService */
    private $flash;

    public function __construct(
        RendererInterface $renderer,
        PostRepository $postRepository,
        Router $router,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->postRepository = $postRepository;
        $this->router = $router;
        $this->flash = $flash;
    }

    /**
     * Action pour afficher tous les posts
     *
     * @param ServerRequestInterface $request
     * @return string
     * @throws NotFoundException
     */
    public function index(ServerRequestInterface $request): string
    {
        $queryParams = $request->getQueryParams();
        $page = $queryParams['page'] ?? 1;
        $perPage = 12;

        $items = $this->postRepository->findPaginated($perPage, $page);

        return $this->renderer->render(
            '@admin/post/index.html.twig',
            [
                'items' => $items,
                'rows' => ($page * $perPage) - $perPage,
            ]
        );
    }

    /**
     * Action pour ajouter un post
     *
     * @param ServerRequestInterface $request
     * @return string
     */
    public function create(ServerRequestInterface $request)
    {
        $errors = null;
        $item = $this->createEntity();

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            $formData = $this->getFilterParseBody($request);

            if ($validator->isValid()) {
                $this->postRepository->insert($formData);
                $this->flash->add('success', 'L\'article a été ajouté');

                return new RedirectResponse(
                    $this->router->generateUri('admin.posts.index')
                );
            }
            $item = self::hydrateFormWithCurrentData($formData, null);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            '@admin/post/create.html.twig',
            [
                'errors' => $errors,
                'item' => $item
            ]
        );
    }

    /**
     * Action pour modifier un post
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     * @throws NotFoundException
     */
    public function edit(ServerRequestInterface $request)
    {
        $item = $this->postRepository->find($request->getAttribute('id'));
        $errors = null;
        $itemName = $item->name;

        if (!$item) {
            throw new NotFoundException(
                sprintf(
                    'Not found entity with id : "%s"',
                    $request->getAttribute('id')
                )
            );
        }

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            $formData = $this->getFilterParseBody($request);

            if ($validator->isValid()) {
                $this->postRepository->update($item->id, $formData);
                $this->flash->add('success', 'L\'article a été modifié');

                return new RedirectResponse(
                    $this->router->generateUri('admin.posts.index')
                );
            }
            $errors = $validator->getErrors();
            $item = self::hydrateFormWithCurrentData($formData, $item);
        }

        return $this->renderer->render(
            '@admin/post/edit.html.twig',
            [
                'item' => $item,
                'errors' => $errors,
                'itemName' => $itemName
            ]
        );
    }

    /**
     * Action pour supprimer un post
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse
     * @throws NotFoundException
     */
    public function delete(ServerRequestInterface $request): RedirectResponse
    {
        $item = $this->postRepository->find($request->getAttribute('id'));
        if (!$item) {
            throw new NotFoundException(
                sprintf(
                    'Not found entity with id : "%s"',
                    $request->getAttribute('id')
                )
            );
        }
        $this->postRepository->delete($item->id);
        $this->flash->add('success', 'L\'article a été supprimé');

        return new RedirectResponse(
            $this->router->generateUri('admin.posts.index')
        );
    }

    /**
     * Filtre des données du ParseBody de la request
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getFilterParseBody(ServerRequestInterface $request): array
    {
        $formData =  array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug', 'content', 'created_at']);
        }, ARRAY_FILTER_USE_KEY);

        return array_merge(
            $formData,
            ['updated_at' => date('Y-m-d H:i:s')]
        );
    }

    /**
     * Initialise le Validator avec les constraintes
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    private function getValidator(ServerRequestInterface $request): Validator
    {
        return (new Validator($request->getParsedBody()))
            ->required(
                ['name' => 'name'],
                ['name' => 'slug'],
                ['name' => 'content'],
                ['name' => 'created_at']
            )
            ->length(
                ['name' => 'content', 'min' => 10],
                ['name' => 'name', 'min' => 5, 'max' => 50],
                ['name' => 'slug', 'min' => 5, 'max' => 50]
            )
            ->slug(
                ['name' => 'slug']
            )
            ->dateTime(
                ['name' => 'created_at']
            );
    }

    /**
     * @param array $data
     * @param object|null $entity
     * @return array
     */
    private static function hydrateFormWithCurrentData(array $data, ?object $entity): array
    {
        if (!\is_null($entity) && \is_object($entity) && property_exists($entity, 'id')) {
            $data['id'] = $entity->id;
            return $data;
        }
        return $data;
    }

    /**
     * @return Post
     */
    private function createEntity(): Post
    {
        $item = new Post();
        $item->created_at = date('Y-m-d H:i:s');
        return $item;
    }
}
