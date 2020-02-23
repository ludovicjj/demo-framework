<?php

namespace Framework\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function is_null;

class TextExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('excerpt', [$this, 'excerpt'])
        ];
    }

    /**
     * Renvoi un extrait, par defaut limité à 100 caracteres.
     *
     * @param string|null $content
     * @param int $length
     * @return string
     */
    public function excerpt(?string $content, int $length = 100): string
    {
        if (is_null($content)) {
            return '';
        }
        if (mb_strlen($content) > $length) {
            $excerpt =  mb_substr($content, 0, $length);
            $lastSpace = mb_strrpos($excerpt, ' ');
            return mb_substr($excerpt, 0, $lastSpace) . '...';
        }

        return $content;
    }
}
