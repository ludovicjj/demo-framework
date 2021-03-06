<?php

namespace Framework\Twig;

use Framework\Database\Pagination\View\CustomView;
use Framework\Router\Router;
use Pagerfanta\Pagerfanta;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaginationExtension extends AbstractExtension
{
    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('paginate', [$this, 'paginate'], ['is_safe' => ['html']])
        ];
    }

    /**
     * @param Pagerfanta $paginatedItems
     * @param string $routeName
     * @param array $routeParams
     * @param array $queryParams
     * @return mixed
     */
    public function paginate(
        Pagerfanta $paginatedItems,
        string $routeName,
        array $routeParams = [],
        array $queryParams = []
    ) {
        $view = new CustomView();
        $view->setRenderCustom(true);

        $options = [
            'proximity' => 2,
            'css_container_class' => 'pagination justify-content-center',
            'prev_message' => '<i class="fas fa-angle-left"></i>',
            'next_message' => '<i class="fas fa-angle-right"></i>',
        ];

        return $view->render($paginatedItems, function (int $page) use ($routeName, $routeParams, $queryParams) {
            if ($page > 1) {
                $queryParams['page'] = $page;
            }
            return $this->router->generateUri($routeName, $routeParams, $queryParams);
        }, $options);
    }
}
