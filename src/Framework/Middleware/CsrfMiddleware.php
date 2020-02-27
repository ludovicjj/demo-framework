<?php

namespace Framework\Middleware;

use Framework\Exceptions\CsrfInvalidException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Exception;

class CsrfMiddleware implements MiddlewareInterface
{

    public $session;

    /** @var string $formKey */
    private $formKey;

    /** @var string $sessionKey */
    private $sessionKey;

    /** @var int $limit */
    private $limit;

    /**
     * @param mixed $session
     * @param string $formKey
     * @param string $sessionKey
     * @param int $limit
     */
    public function __construct(
        $session,
        string $formKey = '_csrf',
        string $sessionKey = 'csrf',
        int $limit = 50
    ) {
        $this->isValidSession($session);
        $this->session = $session;
        $this->formKey = $formKey;
        $this->sessionKey = $sessionKey;
        $this->limit = $limit;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     *
     * @throws CsrfInvalidException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'DELETE', 'PUT'])) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                $this->reject();
            } else {
                $csrfList = $this->session[$this->sessionKey] ?? [];
                if (in_array($params[$this->formKey], $csrfList)) {
                    $this->usedToken($params[$this->formKey]);
                    return $handler->handle($request);
                } else {
                    $this->reject();
                }
            }
        }

        return $handler->handle($request);
    }

    /**
     * Genere un token csrf en session
     *
     * @return string
     */
    public function generateToken(): string
    {
        try {
            $token = bin2hex(random_bytes(16));
            $csrfList = $this->session[$this->sessionKey] ?? [];
            $csrfList[] = $token;
            $this->session[$this->sessionKey] = $csrfList;
            $this->limitToken();
            return $token;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    private function usedToken(string $oldToken): void
    {
        $tokens = array_filter($this->session[$this->sessionKey], function ($listToken) use ($oldToken) {
            return $listToken !== $oldToken;
        });

        $this->session[$this->sessionKey] = $tokens;
    }

    /**
     * @return mixed
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }

    private function limitToken(): void
    {
        $tokens = $this->session[$this->sessionKey] ?? [];
        //TODO tant que nb token > 10
        if (count($tokens) > $this->limit) {
            //TODO extrait la première valeur d'un tableau et la retourne,
            // en raccourcissant le tableau d'un élément,
            // et en déplaçant tous les éléments vers le bas.
            // Toutes les clés numériques seront modifiées pour commencer à zéro.
            array_shift($tokens);
        }
        $this->session[$this->sessionKey] = $tokens;
    }

    private function isValidSession($session)
    {
        if (!is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError('Session must be an array and instance of ArrayAccess');
        }
    }

    /**
     * @throws CsrfInvalidException
     */
    private function reject(): void
    {
        throw new CsrfInvalidException('Missing or invalid token csrf');
    }
}
