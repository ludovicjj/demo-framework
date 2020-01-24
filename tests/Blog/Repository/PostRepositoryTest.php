<?php

namespace Tests\Blog\Repository;

use App\Blog\Entity\Post;
use App\Blog\Repository\PostRepository;
use Tests\DatabaseTestCase;

class PostRepositoryTest extends DatabaseTestCase
{
    /** @var PostRepository */
    private $postRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->postRepository = new PostRepository($this->getPdo());
    }

    public function testFind()
    {
        $this->seed();
        $post = $this->postRepository->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindNotFound()
    {
        $post = $this->postRepository->find(45458878);
        $this->assertNull($post);
    }
}