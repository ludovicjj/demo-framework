<?php

namespace Tests;

use Phinx\Config\Config;
use PHPUnit\Framework\TestCase;
use Phinx\Migration\Manager;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use PDO;

class DatabaseTestCase extends TestCase
{
    /**
     * @param PDO $pdo
     * @return Manager
     */
    private function getManager(PDO $pdo): Manager
    {
        $configArray = require('phinx.php');
        $configArray['environments']['test'] = [
            'adapter'    => 'sqlite',
            'connection' => $pdo
        ];
        $config = new Config($configArray);
        return new Manager($config, new StringInput(' '), new NullOutput());
    }

    protected function getPdo(): PDO
    {
        return new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ
        ]);
    }

    protected function seed(PDO $pdo): void
    {
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
        $this->getManager($pdo)->seed('test');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }

    protected function migrate(PDO $pdo): void
    {
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_BOTH);
        $this->getManager($pdo)->migrate('test');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
    }
}