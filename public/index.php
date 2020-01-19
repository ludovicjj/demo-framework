<?php

use App\Blog\BlogModule;
use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;

require '../vendor/autoload.php';

$renderer = new \App\Framework\Renderer\PHPRenderer();
$renderer->addPath(dirname(__DIR__).'/views');

$app = new App(
    [
        BlogModule::class
    ],
    [
        'renderer' => $renderer
    ]
);

$response = $app->run(ServerRequest::fromGlobals());
send($response);
