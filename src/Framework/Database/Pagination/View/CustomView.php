<?php

namespace Framework\Database\Pagination\View;

use App\Framework\Database\Pagination\View\Template\CustomTemplate;
use App\Framework\Database\Pagination\View\Template\TemplateInterface;
use Pagerfanta\Pagerfanta;

class CustomView
{
    private $template;

    /** @var Pagerfanta */
    private $pagerfanta;
    private $proximity;

    private $currentPage;
    private $nbPages;

    private $startPage;
    private $endPage;

    private $custom;

    public function __construct(TemplateInterface $template = null)
    {
        $this->template = $template ?: $this->createDefaultTemplate();
        $this->custom = false;
    }

    /**
     * Initialize la template par default
     *
     * @return CustomTemplate
     */
    protected function createDefaultTemplate()
    {
        return new CustomTemplate();
    }


    public function render(Pagerfanta $pagerfanta, $routeGenerator, array $options = array())
    {
        $this->initializePagerfanta($pagerfanta);
        $this->initializeOptions($options);
        $this->configureTemplate($routeGenerator, $options);
        return $this->generate();
    }

    /**
     * Recupere l'instance de PagerFanta
     * Recupere la page actuelle
     * Recupere le nombre de page
     *
     * @param Pagerfanta $pagerfanta
     */
    private function initializePagerfanta(Pagerfanta $pagerfanta)
    {
        $this->pagerfanta = $pagerfanta;
        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->nbPages = $pagerfanta->getNbPages();
    }

    /**
     * Recupere la clé "proximity" dans la tableau d'options
     * Si la clé n'est pas presente, utilise la valeur pas default : "2"
     *
     * @param $options
     */
    private function initializeOptions($options)
    {
        $this->proximity = isset($options['proximity']) ?
            (int) $options['proximity'] :
            $this->getDefaultProximity();
    }

    /**
     * Retourne la valeur par default de la clé proximity
     * @return int
     */
    protected function getDefaultProximity()
    {
        return 2;
    }

    /**
     * Ajoute de la definition à la template :
     * Le resultat du callback && le tableau tableau d'option custom
     *
     * @param $routeGenerator
     * @param $options
     */
    private function configureTemplate($routeGenerator, $options)
    {
        $this->template->setRouteGenerator($routeGenerator);
        $this->template->setOptions($options);
    }

    private function generate()
    {
        $pages = $this->generatePages();

        return $this->generateContainer($pages);
    }

    /**
     * Insere le contenu des pages dans le container de la template
     *
     * @param $pages
     * @return mixed
     */
    private function generateContainer($pages)
    {
        return str_replace('%pages%', $pages, $this->template->container());
    }

    private function generatePages()
    {
        $this->calculateStartAndEndPage();
        if ($this->getRenderCustom()) {
            return $this->renderCustomPages();
        }
        return $this->renderPages();
    }

    /**
     * @param bool $option
     */
    public function setRenderCustom(bool $option): void
    {
        $this->custom = $option;
    }

    /**
     * @return bool
     */
    private function getRenderCustom(): bool
    {
        return $this->custom;
    }

    private function renderPages(): string
    {
        return $this->previous().
            $this->first().
            $this->secondIfStartIs3().
            $this->dotsIfStartIsOver3().
            $this->pages().
            $this->dotsIfEndIsUnder3ToLast().
            $this->secondToLastIfEndIs3ToLast().
            $this->last().
            $this->next();
    }

    private function renderCustomPages()
    {
        return $this->previous().
            $this->first().
            $this->dotsIfStartIsOver3().
            $this->pages().
            $this->dotsIfEndIsUnder3ToLast().
            $this->last().
            $this->next();
    }


    private function calculateStartAndEndPage()
    {
        $startPage = $this->currentPage - $this->proximity;
        $endPage = $this->currentPage + $this->proximity;

        if ($this->isUnderFlow($startPage)) {
            $endPage = $this->fixEndPage($startPage, $endPage);
            $startPage = 1;
        }

        if ($this->isOverFlow($endPage)) {
            $startPage = $this->fixStartPage($startPage, $endPage);
            $endPage = $this->nbPages;
        }

        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    /**
     * Vérifie si startPage est inférieur à 1
     *
     * @param int $startPage
     * @return bool
     */
    private function isUnderFlow(int $startPage): bool
    {
        return $startPage < 1;
    }

    /**
     * Vérifie que endPage est inférieur aux nb de pages
     *
     * @param int $endPage
     * @return bool
     */
    private function isOverFlow(int $endPage): bool
    {
        return $endPage > $this->nbPages;
    }

    private function fixEndPage(int $startPage, int $endPage)
    {
        return min($endPage + (1 - $startPage), $this->nbPages);
    }

    private function fixStartPage(int $startPage, int $endPage)
    {
        return max($startPage - ($endPage - $this->nbPages), 1);
    }


    private function previous()
    {
        if ($this->pagerfanta->hasPreviousPage()) {
            return $this->template->previousEnabled($this->pagerfanta->getPreviousPage());
        }

        return $this->template->previousDisabled();
    }

    private function first()
    {
        if ($this->startPage > 1) {
            return $this->template->first();
        }
        return null;
    }

    private function secondIfStartIs3()
    {
        if ($this->startPage == 3) {
            return $this->template->page(2);
        }
        return null;
    }

    private function dotsIfStartIsOver3()
    {
        if ($this->startPage > 3) {
            return $this->template->separator();
        }
        return null;
    }

    private function pages()
    {
        $pages = '';

        foreach (range($this->startPage, $this->endPage) as $page) {
            $pages .= $this->page($page);
        }

        return $pages;
    }

    private function page($page)
    {
        if ($page == $this->currentPage) {
            return $this->template->current($page);
        }

        return $this->template->page($page);
    }

    private function dotsIfEndIsUnder3ToLast()
    {
        if ($this->endPage < $this->toLast(3)) {
            return $this->template->separator();
        }
        return null;
    }

    private function secondToLastIfEndIs3ToLast()
    {
        if ($this->endPage == $this->toLast(3)) {
            return $this->template->page($this->toLast(2));
        }
        return null;
    }

    private function toLast($n)
    {
        return $this->pagerfanta->getNbPages() - ($n - 1);
    }

    private function last()
    {
        if ($this->pagerfanta->getNbPages() > $this->endPage) {
            return $this->template->last($this->pagerfanta->getNbPages());
        }
        return null;
    }

    private function next()
    {
        if ($this->pagerfanta->hasNextPage()) {
            return $this->template->nextEnabled($this->pagerfanta->getNextPage());
        }

        return $this->template->nextDisabled();
    }

    /**
     * Returns the canonical name.
     *
     * @return string The canonical name.
     */
    public function getName()
    {
        return 'custom';
    }
}
