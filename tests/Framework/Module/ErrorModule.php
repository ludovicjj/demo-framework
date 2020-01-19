<?php

namespace Tests\Framework\Module;

use Framework\Router\Router;

class ErrorModule
{
    public function __construct(Router $router)
    {
        $router->get(
            '/demo',
            function () {
                return new \stdClass();
            },
            'demo'
        );
    }
}
