<?php

namespace Framework\Database\Pagination;

use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class PaginatedQuery implements AdapterInterface
{
    /** @var PDO $pdo */
    private $pdo;

    /** @var string $count */
    private $count;

    /** @var string $paginationQuery */
    private $paginationQuery;

    /** @var string|null $entity */
    private $entity;

    /** @var array $params */
    private $params;

    /**
     * PaginatedQuery constructor.
     * @param PDO $pdo
     * @param string $count
     * @param string $paginationQuery
     * @param string|null $entity
     * @param array $params
     */
    public function __construct(
        PDO $pdo,
        string $count,
        string $paginationQuery,
        ?string $entity,
        array $params = []
    ) {
        $this->pdo = $pdo;
        $this->count = $count;
        $this->paginationQuery = $paginationQuery;
        $this->entity = $entity;
        $this->params = $params;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults(): int
    {
        if (!empty($this->params)) {
            $statement =$this->pdo->prepare($this->count);
            $statement->execute($this->params);
            return $statement->fetchColumn();
        }
        return $this->pdo->query($this->count)->fetchColumn();
    }

    /**
     * Retourne les rèsultats pour ce slice.
     * Ajout de la partie limit à la requete initial paginationQuery().
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $statement = $this->pdo->prepare($this->paginationQuery .' LIMIT :offset, :length');

        foreach ($this->params as $field => $value) {
            $statement->bindParam($field, $value);
        }
        $statement->bindParam('offset', $offset, PDO::PARAM_INT);
        $statement->bindParam('length', $length, PDO::PARAM_INT);
        if ($this->entity) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        }

        $statement->execute();
        return $statement->fetchAll();
    }
}
