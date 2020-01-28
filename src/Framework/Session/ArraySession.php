<?php

namespace Framework\Session;

class ArraySession implements SessionInterface
{
    /** @var array */
    private $session = [];

    /**
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
        }

        return $default;
    }

    /**
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * @param string $key
     */
    public function delete(string $key): void
    {
        if (array_key_exists($key, $this->session)) {
            unset($this->session[$key]);
        }
    }
}
