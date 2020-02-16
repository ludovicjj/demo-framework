<?php

namespace Tests\Framework\Twig;

use Framework\Twig\FormExtension;
use Framework\Validator\ValidationError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

class FormExtensionTest extends TestCase
{
    /** @var FormExtension */
    private $formExtension;

    public function setUp(): void
    {
        $this->formExtension = new FormExtension();
    }

    /**
     * Test method getLabel()
     * If label is not define use key
     */
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

    /**
     * Test method getListOption
     * method must return empty array or array with list option
     */
    public function testMethodGetListOption()
    {
        $params1 = ['options' => ['1' => 'option 1', '2' => 'option 2']];
        $params2 = [];

        $this->assertEquals(
            ['1' => 'option 1', '2' => 'option 2'],
            self::callPrivateMethod($this->formExtension, 'getListOption', [$params1])
        );

        $this->assertEquals([], self::callPrivateMethod($this->formExtension, 'getListOption', [$params2]));
    }

    /**
     * Test method initializeAttributes()
     * Method must return array with HTML attributs
     * And update value of key "class" if error
     */
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

    /**
     * Test method buildHtmlErrors()
     * Method must return string with html of bootstrap
     */
    public function testMethodBuildHtmlErrors(): void
    {
        $this->assertNull(
            self::callPrivateMethod($this->formExtension, 'buildHtmlErrors', [null, 'demo'])
        );

        $paramsWithKeyNotMatch =  [['demo' => ['erreur 1']], 'name'];

        $this->assertNull(
            self::callPrivateMethod($this->formExtension, 'buildHtmlErrors', $paramsWithKeyNotMatch)
        );

        $paramsWithOneError = [['demo' => ['erreur 1']], 'demo'];

        $this->assertEquals(
            '<div class="invalid-feedback">erreur 1</div>',
            self::callPrivateMethod($this->formExtension, 'buildHtmlErrors', $paramsWithOneError)
        );

        $paramsWithManyError = [['demo' => ['erreur 1', 'erreur 2']], 'demo'];

        $this->assertEquals(
            '<div class="invalid-feedback">erreur 1</div><div class="invalid-feedback">erreur 2</div>',
            self::callPrivateMethod($this->formExtension, 'buildHtmlErrors', $paramsWithManyError)
        );
    }

    /**
     * Test method buildHtmlFromArray()
     * Method must return string
     */
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

    /**
     * Test getType()
     * Method must return string
     * Method return value of jey "type", if key is not define return default value "text"
     */
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

    /**
     * Test la generation d'un champs "text"
     */
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

    /**
     * Test la generation d'un champs "text" avec erreur
     */
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

    /**
     * Test la generation d'un champs "text" valid
     */
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

    /**
     * Test l'ajoute d'attributs
     */
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
     * Test la generation d'un "select"
     */
    public function testInputSelect()
    {
        $html = $this->formExtension->field(
            [],
            'name',
            '1',
            'categories',
            ['type' => 'select', 'options' => ['1' => 'categorie 1', '2' => 'categorie 2']]
        );

        $this->assertSimilar(
            '<div class="form-group">
                <label for="name">categories</label>
                <select id="name" name="name" class="form-control">
                    <option value="1" selected>categorie 1</option>
                    <option value="2">categorie 2</option>
                </select>
            </div>',
            $html
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
            $class = new ReflectionClass($obj);
            $method = $class->getMethod($name);
            $method->setAccessible(true);
            return $method->invokeArgs($obj, $args);
        } catch (ReflectionException $e) {
            return $e->getMessage();
        }
    }
}
