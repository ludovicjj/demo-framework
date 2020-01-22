<?php

namespace App\Blog\Repository;

class PostRepository
{
    /** @var \PDO */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \stdClass[]
     */
    public function findPaginated(): array
    {
        $statement = $this->pdo->query('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10');
        return $statement->fetchAll();
    }

    /**
     * @param int $id
     * @return bool|\stdClass
     */
    public function find(int $id)
    {
        $statement = $this->pdo->prepare('SELECT * FROM posts WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch();
    }
}
