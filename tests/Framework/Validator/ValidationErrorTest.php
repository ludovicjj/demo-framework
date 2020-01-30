<?php

namespace Tests\Framework\Validator;

use Framework\Validator\ValidationError;
use PHPUnit\Framework\TestCase;

class ValidationErrorTest extends TestCase
{
    private function makeValidatorError(string $property, string $rule, ?string $message, array $options = [])
    {
        return new ValidationError($property, $rule, $message, $options);
    }

    public function testMethodVsprintf()
    {
        $args =[
            'Le champs %s est requis avec %01d caractere',
            'demo',
            3
        ];

        $format = array_shift($args);
        $result = vsprintf($format, $args);

        $this->assertEquals(
            'Le champs demo est requis avec 3 caractere',
            $result
        );
    }

    public function testRequiredCustomMessage(): void
    {
        $this->assertEquals(
            'My custom message',
            $this->makeValidatorError('demo', 'required', 'My custom message')
        );
    }

    public function testRequiredDefaultMessage(): void
    {
        $this->assertEquals(
            'Le champs demo est requis',
            $this->makeValidatorError('demo', 'required', null)
        );
    }

    public function testSlugCustomMessage(): void
    {
        $this->assertEquals(
            'My custom message',
            $this->makeValidatorError('demo', 'slug', 'My custom message')
        );
    }

    public function testSlugDefaultMessage(): void
    {
        $this->assertEquals(
            'Le champs demo n\'est pas un slug valide',
            $this->makeValidatorError('demo', 'slug', null)
        );
    }

    public function testBetweenLengthDefaultMessage()
    {
        $this->assertEquals(
            'Le champs demo doit contenir entre 4 et 7 caractères',
            $this->makeValidatorError('demo', 'betweenLength', null, [4, 7])
        );
    }

    public function testMinLengthDefaultMessage()
    {
        $this->assertEquals(
            'Le champs demo doit contenir plus de 4 caractères',
            $this->makeValidatorError('demo', 'minLength', null, [4])
        );
    }

    public function testMaxLengthDefaultMessage()
    {
        $this->assertEquals(
            'Le champs demo doit contenir moins de 4 caractrères',
            $this->makeValidatorError('demo', 'maxLength', null, [4])
        );
    }

    public function testMaxLengthCustomMessage()
    {
        $this->assertEquals(
            'my custom message',
            $this->makeValidatorError('demo', 'maxLength', 'my custom message')
        );
    }
}
