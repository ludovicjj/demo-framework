<?php

namespace Tests\Framework\Twig;

use Framework\Twig\TextExtension;
use PHPUnit\Framework\TestCase;

class TextExtensionTest extends TestCase
{
    /** @var TextExtension */
    private $extension;

    public function setUp(): void
    {
        $this->extension = new TextExtension();
    }

    public function testExcerptWithShortContent()
    {
        $content = 'my shot text';
        $excerpt = $this->extension->excerpt($content, 15);
        $this->assertEquals('my shot text', $excerpt);
    }

    public function testExcerptWithLongContent()
    {
        $content = 'my shot text';
        $excerpt = $this->extension->excerpt($content, 10);
        $this->assertEquals('my shot...', $excerpt);
    }
}