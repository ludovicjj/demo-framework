<?php

namespace Framework;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->getPath();

        //TODO case : slash
        if (!empty($uri) && $uri !== '/' && $uri[-1] === '/') {
            $response = (new Response())
                ->withStatus(301)
                ->withHeader('Location', substr($uri, 0, -1))
            ;
            return $response;
        }

        //TODO case uri /blog
        if ($uri === "/blog") {
            return new Response(200, [], '<h1>Bienvenue sur le Blog</h1>');
        }

        return new Response(404, [], '<h1>Erreur 404</h1>');
    }
}
