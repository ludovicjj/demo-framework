<?php

namespace Framework\Session;

class FlashService
{
    /** @var SessionInterface */
    private $session;

    /** @var string */
    private $sessionKey = 'flash__';

    private $message;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Ajoute une information en session sous la forme key => value
     *
     * @param string $key
     * @param string $message
     */
    public function add(string $key, string $message): void
    {
        $flash = $this->session->get($this->sessionKey, []);
        $flash[$key] = $message;
        $this->session->set($this->sessionKey, $flash);
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function get(string $key): ?string
    {
        if (\is_null($this->message)) {
            $this->message = $this->session->get($this->sessionKey, []);
            $this->session->delete($this->sessionKey);
        }

        if (array_key_exists($key, $this->message)) {
            return $this->message[$key];
        }
        return null;
    }
}
