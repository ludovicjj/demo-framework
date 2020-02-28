<?php

namespace Framework\Renderer;

use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;

class TwigRendererFactory
{
    public function __invoke(ContainerInterface $container): TwigRenderer
    {
        $debug = $container->get('env') !== 'prod';
        $loader = new FilesystemLoader($container->get('views.path'));
        $twig = new Environment(
            $loader,
            [
                'debug' => $debug,
                'cache' => $debug ? false : 'tmp/views',
                'auto_reload ' => $debug,
            ]
        );
        $twig->addExtension(new DebugExtension());
        if ($container->has('twig.extensions')) {
            foreach ($container->get('twig.extensions') as $extension) {
                $twig->addExtension($extension);
            }
        }

        return new TwigRenderer($twig);
    }
}
