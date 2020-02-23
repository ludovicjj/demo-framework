<?php

namespace Tests\Framework\Database\Repository;

use Framework\Database\Repository\Repository;
use PHPUnit\Framework\TestCase;
use \PDO;
use Framework\Exceptions\NotFoundException;

class RepositoryTest extends TestCase
{
    /** @var Repository */
    private $repository;

    public function setUp(): void
    {
        $pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        ]);
        $sql = "CREATE table test (id INTEGER PRIMARY KEY AUTOINCREMENT, name VARCHAR( 255 ))";

        $pdo->exec($sql);

        $this->repository = new Repository($pdo);
        try {
            $reflexion = new \ReflectionClass($this->repository);
            $property = $reflexion->getProperty('table');
            $property->setAccessible(true);
            $property->setValue($this->repository, 'test');
        } catch (\ReflectionException $e) {
            $e->getMessage();
        }

    }

    /**
     * @throws NotFoundException
     */
    public function testFind()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $records = $this->repository->find(1);
        $this->assertInstanceOf(\stdClass::class, $records);
        $this->assertEquals('a1', $records->name);
    }

    public function testFindList()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $list = $this->repository->findList();
        $this->assertEquals(['1' => 'a1', '2' => 'a2'], $list);
    }

    public function testFindAll()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $records = $this->repository->findAll();
        $this->assertCount(2, $records);
        $this->assertInstanceOf(\stdClass::class, $records[0]);
        $this->assertEquals('a1', $records[0]->name);
        $this->assertEquals('a2', $records[1]->name);
    }

    /**
     * @throws NotFoundException
     */
    public function testFindOneBy()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $record = $this->repository->findOneBy(['name' => 'a1']);
        $this->assertInstanceOf(\stdClass::class, $record);
        $this->assertEquals(1, (int)$record->id);
    }

    public function testExist()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $this->assertTrue($this->repository->exist(1));
        $this->assertTrue($this->repository->exist(2));
        $this->assertFalse($this->repository->exist(3));
    }
}
