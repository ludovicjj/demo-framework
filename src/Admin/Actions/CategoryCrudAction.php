<?php

namespace App\Admin\Actions;

use App\Repository\CategoryRepository;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router\Router;
use Framework\Session\FlashService;
use Framework\Validator\Validator;
use Psr\Http\Message\ServerRequestInterface;

class CategoryCrudAction extends CrudAction
{
    /** @var array $messages */
    protected $messages = [
        'create'    => "La catégorie a bien été ajoutée",
        'edit'      => "La catégorie a bien été modifiée",
        'delete'    => "La catégorie a bien été supprimée"
    ];

    /** @var string $viewPath */
    protected $viewPath = 'admin/categories';

    /** @var string $routePrefixName */
    protected $routePrefixName = 'admin.categories';

    public function __construct(
        RendererInterface $renderer,
        CategoryRepository $repository,
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
        return  array_filter($request->getParsedBody(), function ($key) {
            return in_array($key, ['name', 'slug']);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Recupere le Validator initialisé dans CrudAction::class
     * et lui ajoute les constraintes propre aux categories
     *
     * @param ServerRequestInterface $request
     * @return Validator
     */
    protected function getValidator(ServerRequestInterface $request): Validator
    {
        return parent::getValidator($request)
            ->required(
                ['name' => 'name'],
                ['name' => 'slug']
            )
            ->length(
                ['name' => 'name', 'min' => 5, 'max' => 50],
                ['name' => 'slug', 'min' => 5, 'max' => 50]
            )
            ->unique(
                [
                    'name' => 'slug',
                    'table' => $this->getRepository()->getTable(),
                    'pdo' => $this->getRepository()->getPdo(),
                    'id' => $request->getAttribute('id')
                ]
            )
            ->slug(
                ['name' => 'slug']
            );
    }
}
