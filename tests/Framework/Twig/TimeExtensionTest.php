<?php

namespace Tests\Framework\Twig;

use Framework\Twig\TimeExtension;
use PHPUnit\Framework\TestCase;

class TimeExtensionTest extends TestCase
{
    /** @var TimeExtension */
    private $extension;

    public function setUp(): void
    {
        $this->extension = new TimeExtension();
    }

    public function testDateForma()
    {
        $date = new \DateTime();
        $format = 'd/m/Y H:i';
        $expected = '<time class="timeago" datetime="'.$date->format(\DateTime::ISO8601).'">'. $date->format($format).'</time>';
        $this->assertEquals($expected, $this->extension->ago($date));
    }
}