<?php

namespace Tests\Framework\Database\Repository;

use Framework\Database\Repository\Repository;
use PHPUnit\Framework\TestCase;
use \PDO;

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
        $sql = "CREATE table test (id INTEGER PRIMARY KEY, name VARCHAR( 255 ))";

        $pdo->exec($sql);

        $this->repository = new Repository($pdo);
        $reflexion = new \ReflectionClass($this->repository);
        $property = $reflexion->getProperty('table');
        $property->setAccessible(true);
        $property->setValue($this->repository, 'test');
    }

    public function testFind()
    {
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a1")');
        $this->repository->getPdo()->exec('INSERT INTO test (name) VALUES ("a2")');
        $records = $this->repository->find(1);
        $this->assertInstanceOf(\stdClass::class, $records);
        $this->assertEquals('a1', $records->name);
    }
}