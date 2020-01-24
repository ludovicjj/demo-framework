<?php

namespace Framework\Database\Pagination;

use Traversable;

class Pagination implements \IteratorAggregate
{
    /** @var PaginationBuilder */
    private $builder;
    private $maxPerPage;
    private $currentPage;
    private $nbResults;

    public function __construct(PaginationBuilder $builder)
    {
        $this->builder = $builder;
        $this->maxPerPage = 10;
        $this->currentPage = 1;
    }

    /**
     * Redéfinit le nombre d'item par page
     *
     * @param int $maxPerPage
     * @return $this
     */
    public function setMaxPerPage(int $maxPerPage)
    {
        $this->maxPerPage = $maxPerPage;
        return $this;
    }

    /**
     * Redéfinit la page actuelle
     *
     * @param int $currentPage
     * @return $this
     */
    public function setCurrentPage(int $currentPage)
    {
        $this->currentPage = $currentPage;
        return $this;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        $results = $this->getCurrentPageResultsFromBuilder();

        return new \ArrayIterator($results);
    }

    /**
     * @return array|Traversable
     */
    private function getCurrentPageResultsFromBuilder()
    {
        $offset = $this->calculateOffsetForCurrentPageResults();
        $length = $this->getMaxPerPage();

        return $this->builder->getSlice($offset, $length);
    }

    /**
     * Définit la valeur de la variable offset pour la method getSlice()
     *
     * @return int
     */
    private function calculateOffsetForCurrentPageResults(): int
    {
        return ($this->getCurrentPage() - 1) * $this->getMaxPerPage();
    }

    /**
     * Retourne la page actuelle
     *
     * @return int
     */
    private function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Retourne le nombre d'item par page
     *
     * @return int
     */
    private function getMaxPerPage(): int
    {
        return $this->maxPerPage;
    }

    /**
     * Retourne le nombre de pages
     *
     * @return int
     */
    public function getNbPages(): int
    {
        $nbPages = $this->calculateNbPages();
        if ($nbPages == 0) {
            return $this->minimumNbPages();
        }

        return $nbPages;
    }

    /**
     * @return int
     */
    private function calculateNbPages()
    {
        return (int) ceil($this->getNbResults() / $this->getMaxPerPage());
    }

    /**
     * @return int
     */
    private function minimumNbPages(): int
    {
        return 1;
    }

    /**
     * @return int
     */
    private function getNbResults(): int
    {
        $this->nbResults = $this->builder->getNbResults();
        return $this->nbResults;
    }
}
