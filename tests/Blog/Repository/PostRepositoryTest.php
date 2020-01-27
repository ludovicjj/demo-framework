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

    public function testFind(): void
    {
        $this->seed();
        $post = $this->postRepository->find(1);
        $this->assertInstanceOf(Post::class, $post);
    }

    public function testFindNotFound(): void
    {
        $post = $this->postRepository->find(45458878);
        $this->assertNull($post);
    }

    public function testUpdate(): void
    {
        $this->seed();
        $this->postRepository->update(1, ['name' => 'mon titre', 'slug' => 'demo-slug']);
        $post = $this->postRepository->find(1);
        $this->assertEquals('mon titre', $post->name);
        $this->assertEquals('demo-slug', $post->slug);
    }

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
        $count = $this->getPdo()->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(2, $count);

        $this->postRepository->delete($this->getPdo()->lastInsertId());
        $count = $this->getPdo()->query('SELECT COUNT(id) FROM posts')->fetchColumn();
        $this->assertEquals(1, $count);
    }
}
