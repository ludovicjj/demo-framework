<?php

namespace Tests\Framework\Twig;

use Framework\Twig\FormExtension;
use Framework\Validator\ValidationError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class FormExtensionTest extends TestCase
{
    /** @var FormExtension */
    private $formExtension;

    public function setUp(): void
    {
        $this->formExtension = new FormExtension();
    }

    public function testMethodGetLabel(): void
    {
        $this->assertEquals(
            '<label for="name">mon super label</label>',
            self::callPrivateMethod($this->formExtension, 'getLabel', ['name', 'mon super label'])
        );

        $this->assertEquals(
            '<label for="name">name</label>',
            self::callPrivateMethod($this->formExtension, 'getLabel', ['name'])
        );
    }

    public function testMethodInitializeAttributes(): void
    {
        $this->assertSame(
            ['id' => 'content', 'name' => 'content', 'class' => 'form-control'],
            self::callPrivateMethod($this->formExtension, 'initializeAttributes', ['content', [], null])
        );
        $this->assertSame(
            ['id' => 'content', 'name' => 'content', 'class' => 'form-control is-valid'],
            self::callPrivateMethod($this->formExtension, 'initializeAttributes', ['content', [], []])
        );
        $this->assertSame(
            ['id' => 'content', 'name' => 'content', 'class' => 'form-control is-invalid'],
            self::callPrivateMethod($this->formExtension, 'initializeAttributes', ['content', [], ['error']])
        );
    }

    public function testMethodBuildHtmlErrors(): void
    {
        $this->assertNull(
            self::callPrivateMethod($this->formExtension, 'buildHtmlErrors', [null, 'demo'])
        );

        $this->assertNull(
            self::callPrivateMethod(
                $this->formExtension,
                'buildHtmlErrors',
                [
                    ['demo' => ['error on the floor']],
                    'name'
                ]
            )
        );

        $this->assertEquals(
            '<div class="invalid-feedback">error on the floor</div>',
            self::callPrivateMethod(
                $this->formExtension,
                'buildHtmlErrors',
                [
                    ['demo' => ['error on the floor']],
                    'demo'
                ]
            )
        );
    }

    public function testMethodBuildHtmlFromArray(): void
    {
        $attributes = [
            'id' => 'name',
            'name' => 'name',
            'class' => 'form-control'
        ];

        $this->assertEquals(
            'id="name" name="name" class="form-control"',
            self::callPrivateMethod($this->formExtension, 'buildHtmlFromArray', [$attributes]
            )
        );
    }

    public function testMethodGetType(): void
    {
        $this->assertEquals(
            'textarea',
            self::callPrivateMethod($this->formExtension, 'getType', [['type' => 'textarea']])
        );
        $this->assertEquals(
            'text',
            self::callPrivateMethod($this->formExtension, 'getType', [[]])
        );
    }

    public function testInputText(): void
    {
        $html = $this->formExtension->field([], 'name', 'demo', 'demo');

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <input type="text" id="name" name="name" class="form-control" value="demo">
            </div>',
            $html
        );
    }

    public function testInputTextWithError(): void
    {
        $validatorError = $this->makeMockValidatorError('name', 'ma super erreur');
        $context = ['errors' => [$validatorError]];
        $html = $this->formExtension->field($context, 'name', 'demo', 'demo');

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <input type="text" id="name" name="name" class="form-control is-invalid" value="demo">
                <div class="invalid-feedback">ma super erreur</div>
            </div>',
            $html
        );
    }

    public function testInputTextValid(): void
    {
        $context = ['errors' => []];
        $html = $this->formExtension->field($context, 'name', 'demo', 'demo');

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <input type="text" id="name" name="name" class="form-control is-valid" value="demo">
            </div>',
            $html
        );
    }

    public function testInputTextarea(): void
    {
        $html = $this->formExtension->field([], 'name', 'demo', 'demo', ['type' => 'textarea']);

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <textarea id="name" name="name" class="form-control">demo</textarea>
            </div>',
            $html
        );
    }

    public function testInputTextareaWithError(): void
    {
        $validatorError = $this->makeMockValidatorError('name', 'ma super erreur');
        $context = ['errors' => [$validatorError]];
        $html = $this->formExtension->field($context, 'name', 'demo', 'demo', ['type' => 'textarea']);

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <textarea id="name" name="name" class="form-control is-invalid">demo</textarea>
                <div class="invalid-feedback">ma super erreur</div>
            </div>',
            $html
        );
    }

    public function testInputTextareaValid(): void
    {
        $context = ['errors' => []];
        $html = $this->formExtension->field($context, 'name', 'demo', 'demo', ['type' => 'textarea']);

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <textarea id="name" name="name" class="form-control is-valid">demo</textarea>
            </div>',
            $html
        );
    }

    public function testFieldWithOptionsAttr(): void
    {
        $html = $this->formExtension->field(
            [],
            'name',
            'demo',
            'demo',
            ['attr' => ['class' => 'demo']]
        );

        $html2 = $this->formExtension->field(
            [],
            'name',
            'demo',
            'demo',
            ['type' => 'textarea', 'attr' => ['class' => 'demo', 'rows' => '10']]
        );

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <input type="text" id="name" name="name" class="form-control demo" value="demo">
            </div>',
            $html
        );

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">demo</label>
                <textarea id="name" name="name" class="form-control demo" rows="10">demo</textarea>
            </div>',
            $html2
        );
    }

    /**
     * @param string $expected
     * @param string $actual
     */
    private function assertSimilar(string $expected, string $actual): void
    {
        $this->assertEquals($this->trim($expected), $this->trim($actual));
    }

    /**
     * @param string $lines
     * @return string
     */
    private function trim(string $lines): string
    {
        $arrayLines = explode(PHP_EOL, $lines);
        return  implode('', array_map(function ($value) {
            return trim($value);
        }, $arrayLines));
    }

    private function makeMockValidatorError(string $property, string $message): MockObject
    {
        $validatorError = $this->createMock(ValidationError::class);
        $validatorError->method('getProperty')->willReturn($property);
        $validatorError->method('getMessage')->willReturn($message);
        return $validatorError;
    }

    /**
     * @param mixed $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    private static function callPrivateMethod($obj, string $name, array $args)
    {
        try {
            $class = new \ReflectionClass($obj);
            $method = $class->getMethod($name);
            $method->setAccessible(true);
            return $method->invokeArgs($obj, $args);
        } catch (\ReflectionException $e) {
            return $e->getMessage();
        }
    }
}
