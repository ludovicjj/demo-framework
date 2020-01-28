<?php

namespace App\Admin\Actions;

use App\Blog\Repository\PostRepository;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectResponse;
use Framework\Router\Router;
use Framework\Session\FlashService;
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
        if ($request->getMethod() === 'POST') {
            $data = $this->getFilterParseBody($request);
            $data = array_merge($data, [
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $this->postRepository->insert($data);
            $this->flash->add('success', 'L\'article a été ajouté');

            return new RedirectResponse(
                $this->router->generateUri('admin.posts.index')
            );
        }

        return $this->renderer->render('@admin/post/create.html.twig');
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

        if (!$item) {
            throw new NotFoundException(
                sprintf(
                    'Not found entity with id : "%s"',
                    $request->getAttribute('id')
                )
            );
        }

        if ($request->getMethod() === 'POST') {
            $data = $this->getFilterParseBody($request);
            $data = array_merge($data, [
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $this->postRepository->update($item->id, $data);
            $this->flash->add('success', 'L\'article a été modifié');

            return new RedirectResponse(
                $this->router->generateUri('admin.posts.index')
            );
        }

        return $this->renderer->render(
            '@admin/post/edit.html.twig',
            ['item' => $item]
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
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getFilterParseBody(ServerRequestInterface $request): array
    {
        return array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug', 'content']);
        }, ARRAY_FILTER_USE_KEY);
    }
}
