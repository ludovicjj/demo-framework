<?php

namespace Framework\Twig;

use Framework\Middleware\CsrfMiddleware;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CsrfExtension extends AbstractExtension
{
    /** @var CsrfMiddleware $middleware */
    private $middleware;

    public function __construct(
        CsrfMiddleware $middleware
    ) {
        $this->middleware = $middleware;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']])
        ];
    }

    public function csrfInput()
    {
        return '<input type="hidden" ' .
            'name="' . $this->middleware->getFormKey() . '" '.
            'value="' . $this->middleware->generateToken() . '">';
    }
}
