<?php

namespace App\Repository;

use App\Entity\Post;
use Framework\Database\Pagination\PaginatedQuery;
use Framework\Database\Repository\Repository;
use Framework\Exceptions\NotFoundException;
use Pagerfanta\Pagerfanta;

class PostRepository extends Repository
{
    protected $table = 'posts';

    protected $entity = Post::class;

    /**
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     * @throws NotFoundException
     */
    public function findPaginatedPublic(int $perPage, int $currentPage): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->getPdo(),
            "SELECT COUNT(id) FROM {$this->table}",
            "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} as p
            LEFT JOIN categories as c
            ON p.category_id = c.id
            ORDER BY created_at DESC",
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

    public function findPaginatedPublicForCategory(int $perPage, int $currentPage, int $categoryId): Pagerfanta
    {
        $query = new PaginatedQuery(
            $this->getPdo(),
            "SELECT COUNT(id) FROM {$this->table} as p WHERE p.category_id=:category_id",
            "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} as p
            LEFT JOIN categories as c
            ON p.category_id = c.id
            WHERE p.category_id=:category_id
            ORDER BY created_at DESC",
            $this->entity,
            ['category_id' => $categoryId]
        );

        return (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    /**
     * @param array $criteria
     * @throws NotFoundException
     * @return Post
     */
    public function findWithCategory(array $criteria): Post
    {
        return $this->fetchOrFail(
            "SELECT p.*, c.name as category_name, c.slug as category_slug
            FROM {$this->table} as p
            LEFT JOIN categories as c ON p.category_id = c.id
            WHERE p.id = :id",
            $criteria
        );
    }

    protected function paginationQuery(): string
    {
        return "SELECT p.id, p.name, c.name as category_name
        FROM {$this->table} as p
        LEFT JOIN categories as c
        ON p.category_id = c.id
        ORDER BY created_at DESC";
    }
}
