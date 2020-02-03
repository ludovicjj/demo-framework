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
        return parent::paginationQuery() . " ORDER BY created_at DESC LIMIT :offset, :length";
    }
}
