<?php

namespace Framework\Database\Pagination;

class PaginationBuilder
{
    /** @var \PDO */
    private $pdo;

    /** @var string */
    private $count;

    /** @var string */
    private $query;

    /** @var string */
    private $entity;

    /**
     * PaginatedQuery constructor.
     * @param \PDO $pdo
     * @param string $count
     * @param string $query
     * @param string $entity
     */
    public function __construct(
        \PDO $pdo,
        string $count,
        string $query,
        string $entity
    ) {
        $this->pdo = $pdo;
        $this->count = $count;
        $this->query = $query;
        $this->entity = $entity;
    }

    /**
     * Returns the number of results.
     *
     * @return integer The number of results.
     */
    public function getNbResults(): int
    {
        return $this->pdo->query($this->count)->fetchColumn();
    }

    /**
     * Returns an slice of the results.
     *
     * @param integer $offset The offset.
     * @param integer $length The length.
     *
     * @return array|\Traversable The slice.
     */
    public function getSlice($offset, $length)
    {
        $statement = $this->pdo->prepare($this->query);
        $statement->bindParam(':offset', $offset, \PDO::PARAM_INT);
        $statement->bindParam(':length', $length, \PDO::PARAM_INT);
        $statement->setFetchMode(\PDO::FETCH_CLASS, $this->entity);

        $statement->execute();
        return $statement->fetchAll();
    }
}
