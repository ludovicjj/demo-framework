<?php

namespace Tests\Blog\Repository;

use App\Entity\Post;
use App\Repository\PostRepository;
use Framework\Exceptions\NotFoundException;
use Tests\DatabaseTestCase;
use PDO;

class PostRepositoryTest extends DatabaseTestCase
{
    /** @var PostRepository */
    private $postRepository;

    /** @var PDO $pdo */
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = $this->getPdo();
        $this->migrate($this->pdo);
        $this->postRepository = new PostRepository($this->pdo);
    }

    /**
     * @throws NotFoundException
     */
    public function testFind(): void
    {
        $this->seed($this->pdo);
        $post = $this->postRepository->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    /**
     * @throws NotFoundException
     */
    public function testFindException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->postRepository->find(45458878);
    }

    /**
     * @throws NotFoundException
     */
    public function testUpdate(): void
    {
        $this->seed($this->pdo);
        $this->postRepository->update(1, ['name' => 'mon titre', 'slug' => 'demo-slug']);
        $post = $this->postRepository->find(1);
        $this->assertEquals('mon titre', $post->name);
        $this->assertEquals('demo-slug', $post->slug);
    }

    /**
     * @throws NotFoundException
     */
    public function testInsert(): void
    {
        $this->postRepository->insert(['name' => 'mon titre', 'slug' => 'demo-slug']);
        $post = $this->postRepository->find(1);
        $this->assertEquals('mon titre', $post->name);
        $this->assertEquals('demo-slug', $post->slug);
    }

    public function testDelete(): void
    {
        $this->postRepository->insert(['name' => 'mon titre', 'slug' => 'demo-slug']);
        $this->postRepository->insert(['name' => 'mon titre', 'slug' => 'demo-slug']);
        $count = $this->pdo->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(2, $count);

        $this->postRepository->delete($this->pdo->lastInsertId());
        $count = $this->pdo->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(1, $count);
    }
}
