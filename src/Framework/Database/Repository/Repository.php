<?php

namespace Framework\Database\Repository;

use Framework\Database\Pagination\PaginatedQuery;
use Framework\Exceptions\NotFoundException;
use Pagerfanta\Pagerfanta;

class Repository
{
    /** @var \PDO */
    private $pdo;

    /**
     * Nom de la table en BDD
     *
     * @var string
     */
    protected $table;

    /** @var string|null */
    protected $entity;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     * @throws NotFoundException
     */
    public function findPaginated(int $perPage, int $currentPage): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->pdo,
            "SELECT COUNT(id) FROM {$this->table}",
            $this->paginationQuery(),
            $this->entity
        );

        $maxPage = (new Pagerfanta($query))->setMaxPerPage($perPage)->getNbPages();

        if ($currentPage < 1 || $currentPage > $maxPage) {
            throw new NotFoundException(
                sprintf('Page %d not found', $currentPage)
            );
        }

        return (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * @return string
     */
    protected function paginationQuery(): string
    {
        return "SELECT * FROM {$this->table}";
    }

    /**
     * Recupere un élément par son id
     *
     * @param int $entityId
     * @return mixed
     */
    public function find(int $entityId)
    {
        $statement = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $entityId]);

        if ($this->entity) {
            $statement->setFetchMode(\PDO::FETCH_CLASS, $this->entity);
        }

        return $statement->fetch() ?: null;
    }

    /**
     * Met à jour les champs d'un élément par son id
     *
     * @param int $entityId
     * @param array $data
     * @return bool
     */
    public function update(int $entityId, array $data): bool
    {
        $fieldQuery = $this->buildFieldQuery($data);
        $data['id'] = $entityId;
        $statement = $this->pdo->prepare("UPDATE {$this->table} SET {$fieldQuery} WHERE id=:id");
        return $statement->execute($data);
    }

    /**
     * Ajoute un élément
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        $fields = join(', ', array_keys($data));

        $values = join(', ', array_map(function ($key) {
            return ":$key";
        }, array_keys($data)));

        $statement = $this->pdo->prepare("INSERT INTO {$this->table} ($fields) VALUES ($values)");
        return $statement->execute($data);
    }

    /**
     * Supprime un élément
     *
     * @param int $entityId
     * @return bool
     */
    public function delete(int $entityId): bool
    {
        $query = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $query->execute(['id' => $entityId]);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string|null
     */
    public function getEntity(): ?string
    {
        return $this->entity;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    private function buildFieldQuery(array $data): string
    {
        $arrayData =  array_map(function ($key) {
            return "$key=:$key";
        }, array_keys($data));

        return join(', ', $arrayData);
    }
}
