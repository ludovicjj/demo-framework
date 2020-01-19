<?php

namespace Tests\Framework\Renderer;

use App\Framework\Renderer\PHPRenderer;
use PHPUnit\Framework\TestCase;

class PHPRendererTest extends TestCase
{
    /** @var PHPRenderer */
    private $renderer;

    public function setUp(): void
    {
        $this->renderer = new PHPRenderer();
        $this->renderer->addPath(dirname(__DIR__).'/views');
    }

    public function testRenderWithoutNamespace()
    {
        $content = $this->renderer->render('test_view.php');
        $this->assertEquals('<h1>Ma super template</h1>', $content);
    }

    public function testRenderWithCustomNamespace()
    {
        $this->renderer->addPath(dirname(__DIR__).'/views', 'test');
        $content = $this->renderer->render('@test/test_view.php');
        $this->assertEquals('<h1>Ma super template</h1>', $content);
    }

    public function testRenderWithParameters()
    {
        $content = $this->renderer->render(
            'test_view_with_params.php',
            [
                'name' => 'demo'
            ]
        );
        $this->assertEquals('Le nom de la template est demo', $content);
    }

    public function testRenderWithGlobalParameters()
    {
        $this->renderer->addGlobal('name', 'demo');
        $content = $this->renderer->render('test_view_with_params.php');
        $this->assertEquals('Le nom de la template est demo', $content);
    }
}