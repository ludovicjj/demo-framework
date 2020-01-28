<?php

namespace Tests\Framework\Session;

use Framework\Session\ArraySession;
use Framework\Session\FlashService;
use PHPUnit\Framework\TestCase;

class FlashServiceTest extends TestCase
{
    /** @var FlashService */
    private $flash;

    /** @var ArraySession */
    private $session;

    public function setUp(): void
    {
        $this->session = new ArraySession();
        $this->flash = new FlashService($this->session);
    }

    public function testDeleteFlashMessageAfterDisplayIt()
    {
        $this->flash->add('success', 'bravo');

        $this->assertEquals('bravo', $this->flash->get('success'));
        $this->assertNull($this->session->get('flash__'));
        $this->assertEquals('bravo', $this->flash->get('success'));
        $this->assertEquals('bravo', $this->flash->get('success'));
    }
}