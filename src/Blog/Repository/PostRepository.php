<?php

namespace App\Blog\Repository;

use App\Blog\Entity\Post;
use Framework\Database\Pagination\PaginatedQuery;
use Framework\Exceptions\NotFoundException;
use Pagerfanta\Pagerfanta;

class PostRepository
{
    /** @var \PDO */
    private $pdo;

    /**
     * PostRepository constructor.
     * @param \PDO $pdo
     */
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
            'SELECT COUNT(id) FROM posts',
            'SELECT * FROM posts ORDER BY created_at DESC LIMIT :offset, :length',
            Post::class
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
     * Recupere une entité par son id
     *
     * @param int $entityId
     * @return null|Post
     */
    public function find(int $entityId): ?Post
    {
        $statement = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $statement->execute(['id' => $entityId]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, Post::class);
        return $statement->fetch() ?: null;
    }

    /**
     * Met à jour une entité par son id
     *
     * @param int $entityId
     * @param array $data
     * @return bool
     */
    public function update(int $entityId, array $data): bool
    {
        $fieldQuery = $this->buildFieldQuery($data);
        $data['id'] = $entityId;
        $statement = $this->pdo->prepare("UPDATE posts SET $fieldQuery WHERE id=:id");
        return $statement->execute($data);
    }

    /**
     * Ajoute une entité
     *
     * @param array $data
     * @return bool
     */
    public function insert(array $data): bool
    {
        $values = join(', ', array_map(function ($value) {
            return ":$value";
        }, array_keys($data)));

        $fields = join(', ', array_keys($data));

        $statement = $this->pdo->prepare("INSERT INTO posts ($fields) VALUES ($values)");
        return $statement->execute($data);
    }

    /**
     * Supprime une entité
     *
     * @param int $entityId
     * @return bool
     */
    public function delete(int $entityId): bool
    {
        $query = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        return $query->execute(['id' => $entityId]);
    }

    private function buildFieldQuery(array $data): string
    {
        $arrayData =  array_map(function ($key) {
            return "$key=:$key";
        }, array_keys($data));

        return join(', ', $arrayData);
    }
}
