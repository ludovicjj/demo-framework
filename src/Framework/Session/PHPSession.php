<?php

namespace Framework\Session;

class PHPSession implements SessionInterface
{
    /**
     * Recupere une information en session
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->ensureSession();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    /**
     * Ajoute une information en session
     *
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void
    {
        $this->ensureSession();
        $_SESSION[$key] = $value;
    }

    /**
     * Supprime une information en session
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->ensureSession();
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Assure que la session est active
     */
    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
