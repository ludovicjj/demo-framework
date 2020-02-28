<?php

namespace Framework\Router;

use Psr\Container\ContainerInterface;

class RouterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $cache = null;

        if ($container->get('env') === 'prod') {
            $cache = 'tmp/routes';
        }
        return new Router($cache);
    }
}
