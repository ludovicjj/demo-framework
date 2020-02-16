<?php

namespace App\Repository;

use App\Entity\Post;
use Framework\Database\Repository\Repository;

class PostRepository extends Repository
{
    protected $table = 'posts';

    protected $entity = Post::class;

    protected function paginationQuery(): string
    {
        return "SELECT p.*, c.name as category_name
        FROM {$this->table} as p
        LEFT JOIN categories as c
        ON p.category_id = c.id
        ORDER BY created_at DESC
        LIMIT :offset, :length";
    }
}
