<?php

namespace Framework\Database\Repository;

use Framework\Database\Pagination\PaginatedQuery;
use Framework\Exceptions\NotFoundException;
use Pagerfanta\Pagerfanta;
use \PDO;

class Repository
{
    /** @var PDO */
    private $pdo;

    /**
     * Nom de la table en BDD
     *
     * @var string
     */
    protected $table;

    /** @var string|null */
    protected $entity;

    public function __construct(PDO $pdo)
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
     * @throws NotFoundException
     * @return mixed
     */
    public function find(int $entityId)
    {
        return $this->fetchOrFail("SELECT * FROM {$this->table} WHERE id = :id", ['id' => $entityId]);
    }

    /**
     * Recupere tous les éléments
     *
     * @return array
     */
    public function findAll()
    {
        $statement = $this->pdo->query("SELECT * FROM {$this->table}");
        if ($this->entity) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        } else {
            $statement->setFetchMode(PDO::FETCH_OBJ);
        }
        return $statement->fetchAll();
    }

    /**
     * @param array $criteria
     * @throws NotFoundException
     * @return mixed
     */
    public function findOneBy(array $criteria)
    {
        $query = "SELECT * FROM {$this->table} WHERE {$this->buildFieldQuery($criteria)}";

        return $this->fetchOrFail($query, $criteria);
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
        $statement = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $statement->execute(['id' => $entityId]);
    }

    /**
     * Retourne un tableau avec la liste d'element sous la forme suivante :
     * key : id de l'element
     * value : name de l'élément
     */
    public function findList()
    {
        $arrayRecords = $this->pdo->query("SELECT id, name FROM {$this->table}")
            ->fetchAll(PDO::FETCH_NUM);
        $list = [];

        foreach ($arrayRecords as $record) {
            $list[$record[0]] = $record[1];
        }

        return $list;
    }

    /**
     * Vérifie qu'un element exist en BDD
     *
     * @param int $id
     * @return bool
     */
    public function exist(int $id): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id = :id");
        $statement->execute(['id' => $id]);
        return $statement->fetchColumn() !== false;
    }

    /**
     * Recupere le nombre d'éléments
     *
     * @return int
     */
    public function count(): int
    {
        return $this->fetchColumn("SELECT COUNT(id) FROM {$this->table}");
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
     * @return PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Permet de recupere le premier element
     *
     * @param string $query
     * @param array $criteria
     * @throws NotFoundException
     * @return mixed
     */
    protected function fetchOrFail(string $query, array $criteria = [])
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($criteria);

        if ($this->entity) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        } else {
            $statement->setFetchMode(PDO::FETCH_OBJ);
        }
        $record = $statement->fetch();

        if ($record === false) {
            $message = 'Not found entity with ';
            foreach ($criteria as $field => $value) {
                $message .= sprintf('%s : %s', $field, $value);
            }
            throw new NotFoundException(
                $message
            );
        }

        return $record;
    }

    /**
     * Recupere la premiere colonne
     *
     * @param string $query
     * @param array $criteria
     * @return mixed
     */
    protected function fetchColumn(string $query, array $criteria = [])
    {
        $statement = $this->pdo->prepare($query);
        $statement->execute($criteria);
        if ($this->entity) {
            $statement->setFetchMode(PDO::FETCH_CLASS, $this->entity);
        } else {
            $statement->setFetchMode(PDO::FETCH_OBJ);
        }
        return $statement->fetchColumn();
    }

    private function buildFieldQuery(array $criteria): string
    {
        $fields =  array_map(function ($key) {
            return "$key=:$key";
        }, array_keys($criteria));

        return join(', ', $fields);
    }
}
