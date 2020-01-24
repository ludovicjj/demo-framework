<?php

namespace Tests;

use Phinx\Config\Config;
use PHPUnit\Framework\TestCase;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use \PDO;

class DatabaseTestCase extends TestCase
{
    /** @var Manager */
    private $manager;

    /** @var PDO */
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $configArray = require('phinx.php');
        $configArray['environments']['test'] = [
            'adapter'    => 'sqlite',
            'connection' => $this->pdo
        ];
        $config = new Config($configArray);
        $this->manager = new Manager($config, new StringInput(' '), new NullOutput());
        $this->migrate();
        // You can change default fetch mode after the seeding
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    protected function seed(): void
    {
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
        $this->manager->seed('test');
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    protected function getPdo()
    {
        return $this->pdo;
    }

    private function migrate(): void
    {
        $this->manager->migrate('test');
    }
}