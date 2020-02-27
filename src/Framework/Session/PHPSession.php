<?php

namespace Framework\Session;

use ArrayAccess;

class PHPSession implements SessionInterface, ArrayAccess
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $this->ensureSession();
        return (array_key_exists($offset, $_SESSION));
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        $this->delete($offset);
    }
}
