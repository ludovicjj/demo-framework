<?php

use App\Admin\AdminModule;
use App\Blog\BlogModule;
use Framework\Middleware\CsrfMiddleware;
use Framework\Middleware\NotFoundMiddleware;
use Framework\Middleware\TrailingSlashMiddleware;
use Framework\Middleware\MethodMiddleware;
use Framework\Middleware\RouterMiddleware;
use Framework\Middleware\DispatcherMiddleware;
use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$modules = [
    AdminModule::class,
    BlogModule::class
];

$app = (new App('config/config.php'))
    ->addModule(AdminModule::class)
    ->addModule(BlogModule::class)
    ->pipe(TrailingSlashMiddleware::class)
    ->pipe(MethodMiddleware::class)
    ->pipe(CsrfMiddleware::class)
    ->pipe(RouterMiddleware::class)
    ->pipe(DispatcherMiddleware::class)
    ->pipe(NotFoundMiddleware::class);

if (php_sapi_name() !== 'cli') {
    $response = $app->run(ServerRequest::fromGlobals());
    send($response);
}
