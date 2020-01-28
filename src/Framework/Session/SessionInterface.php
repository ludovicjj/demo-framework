<?php

namespace Framework\Session;

interface SessionInterface
{
    /**
     * Recupere une information en session
     *
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Ajoute une information en session
     *
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void;


    /**
     * Supprime une information en session
     *
     * @param string $key
     */
    public function delete(string $key): void;
}
