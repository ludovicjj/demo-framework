<?php

namespace Framework\Actions;

use Framework\Database\Repository\Repository;
use Framework\Exceptions\NotFoundException;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router\Router;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;

class CrudAction
{
    /** @var RendererInterface */
    private $renderer;

    /** @var Repository */
    private $repository;

    /** @var Router */
    private $router;

    /** @var FlashService */
    private $flash;

    /** @var string */
    protected $viewPath;

    /** @var string */
    protected $routePrefixName;

    /** @var array */
    protected $messages = [
        'create'    => "L'élément a bien été ajouté",
        'edit'      => "L'élément a bien été modifié",
        'delete'    => "L'élément a bien été supprimé"
    ];

    /**
     * CrudAction constructor.
     * @param RendererInterface $renderer
     * @param Repository $repository
     * @param Router $router
     * @param FlashService $flash
     */
    public function __construct(
        RendererInterface $renderer,
        Repository $repository,
        Router $router,
        FlashService $flash
    ) {
        $this->renderer = $renderer;
        $this->repository = $repository;
        $this->router = $router;
        $this->flash = $flash;
    }

    /**
     * Action pour afficher tous les elements
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

        $items = $this->repository->findPaginated($perPage, $page);

        return $this->renderer->render(
            $this->viewPath . '/index.html.twig',
            [
                'items' => $items,
                'rows' => ($page * $perPage) - $perPage,
            ]
        );
    }

    /**
     * Action pour ajouter un element
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     */
    public function create(ServerRequestInterface $request)
    {
        $errors = null;
        $item = $this->getNewEntity($this->repository->getEntity());

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            $formData = $this->getFilterParseBody($request);

            if ($validator->isValid()) {
                $this->repository->insert($formData);
                $this->flash->add('success', $this->messages['create']);

                return new RedirectResponse(
                    $this->router->generateUri($this->routePrefixName . '.index')
                );
            }
            $item = self::hydrateFormWithCurrentData($formData, null);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/create.html.twig',
            [
                'errors' => $errors,
                'item' => $item
            ]
        );
    }

    /**
     * Action pour modifier un element
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse|string
     * @throws NotFoundException
     */
    public function edit(ServerRequestInterface $request)
    {
        $item = $this->repository->find($request->getAttribute('id'));
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
                $this->repository->update($item->id, $formData);
                $this->flash->add('success', $this->messages['edit']);

                return new RedirectResponse(
                    $this->router->generateUri($this->routePrefixName . '.index')
                );
            }
            $errors = $validator->getErrors();
            $item = self::hydrateFormWithCurrentData($formData, $item);
        }

        return $this->renderer->render(
            $this->viewPath . '/edit.html.twig',
            [
                'item' => $item,
                'errors' => $errors,
                'itemName' => $itemName
            ]
        );
    }

    /**
     * Action pour supprimer un element
     *
     * @param ServerRequestInterface $request
     * @return RedirectResponse
     * @throws NotFoundException
     */
    public function delete(ServerRequestInterface $request): RedirectResponse
    {
        $item = $this->repository->find($request->getAttribute('id'));
        if (!$item) {
            throw new NotFoundException(
                sprintf(
                    'Not found entity with id : "%s"',
                    $request->getAttribute('id')
                )
            );
        }
        $this->repository->delete($item->id);
        $this->flash->add('success', $this->messages['delete']);

        return new RedirectResponse(
            $this->router->generateUri($this->routePrefixName . '.index')
        );
    }

    /**
     * Filtre des données du ParseBody de la request
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getFilterParseBody(ServerRequestInterface $request): array
    {
        $formData =  array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, []);
        }, ARRAY_FILTER_USE_KEY);

        return $formData;
    }

    /**
     * Initialise le Validator
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return new Validator($request->getParsedBody());
    }

    /**
     * Hydrate la propriété "created_at"
     *
     * @param string|null $class
     * @return null|mixed
     */
    protected function getNewEntity(?string $class)
    {
        if (!\is_null($class) && class_exists($class) && property_exists($class, 'created_at')) {
            $entity = new $class();
            $entity->created_at = date('Y-m-d H:i:s');
            return $entity;
        }
        return null;
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
}
