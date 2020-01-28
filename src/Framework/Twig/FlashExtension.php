<?php

namespace Framework\Twig;

use Framework\Session\FlashService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FlashExtension extends AbstractExtension
{
    /** @var FlashService */
    private $flash;

    public function __construct(FlashService $flash)
    {
        $this->flash = $flash;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('flash', [$this, 'flash'])
        ];
    }

    /**
     * @param string $key
     * @return string|null
     */
    public function flash(string $key): ?string
    {
        return $this->flash->get($key);
    }
}
