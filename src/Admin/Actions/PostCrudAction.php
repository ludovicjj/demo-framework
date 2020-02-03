<?php

namespace App\Admin\Actions;

use App\Repository\PostRepository;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;

class PostCrudAction extends CrudAction
{
    /** @var array */
    protected $messages = [
        'create'    => "L'article a bien été ajouté",
        'edit'      => "L'article a bien été modifié",
        'delete'    => "L'article a bien été supprimé"
    ];

    protected $viewPath = 'admin/posts';

    protected $routePrefixName = 'admin.posts';

    public function __construct(
        RendererInterface $renderer,
        PostRepository $repository,
        Router $router,
        FlashService $flash
    ) {
        parent::__construct($renderer, $repository, $router, $flash);
    }

    /**
     * Filtre des données du ParseBody de la request
     * Overrides getFilterParseBody() de CrudAction::action
     *
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function getFilterParseBody(ServerRequestInterface $request): array
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
     * Recupere le Validator initialisé dans CrudAction::class
     * et lui ajoute les constraintes propre aux posts
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
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
     * Hydrate la propriété "created_at"
     * @param string|null $class
     * @return null|mixed
     */
    protected function getNewEntity(?string $class)
    {
        if (class_exists($class) && property_exists($class, 'created_at')) {
            $entity = new $class();
            $entity->created_at = date('Y-m-d H:i:s');
            return $entity;
        }
        return null;
    }
}
