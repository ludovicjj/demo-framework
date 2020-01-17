<?php

use Framework\App;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;

require '../vendor/autoload.php';

$app = new App();

$response = $app->run(ServerRequest::fromGlobals());
send($response);
