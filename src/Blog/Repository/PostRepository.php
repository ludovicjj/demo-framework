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
     * @param int $id
     * @return null|Post
     */
    public function find(int $id): ?Post
    {
        $statement = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $statement->execute(['id' => $id]);
        $statement->setFetchMode(\PDO::FETCH_CLASS, Post::class);
        return $statement->fetch() ?: null;
    }
}
