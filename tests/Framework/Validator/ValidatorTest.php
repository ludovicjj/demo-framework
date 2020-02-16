<?php

namespace Tests\Framework\Validator;

use Framework\Validator\Validator;
use Tests\DatabaseTestCase;

class ValidatorTest extends DatabaseTestCase
{
    public function testManyConstraintsOnOneKey(): void
    {
        $validatorErrorList = $this->makeValidator(['content' => ''])
            ->notEmpty(['name' => 'content'])
            ->slug(['name' => 'content'])
            ->getErrors();
        $this->assertCount(2, $validatorErrorList);
    }

    public function testMethodRequiredSuccess(): void
    {
        $validatorErrorList = $this->makeValidator(['name' => 'john', 'content' => 'mon contenu'])
            ->required(
                ['name' => 'name', 'message' => 'Le champs titre est requis'],
                ['name' => 'content']
            )
            ->getErrors();

        $this->assertCount(0, $validatorErrorList);
    }

    public function testMethodRequiredFail(): void
    {
        $validatorErrorList = $this->makeValidator(['name' => 'john'])
            ->required(
                ['name' => 'name'],
                ['name' => 'content']
            )
            ->getErrors();

        $this->assertCount(1, $validatorErrorList);
    }

    public function testMethodSlugSuccess(): void
    {
        $validatorErrorList = $this->makeValidator(['slug' => 'demo-demo-demo'])->slug(['name' => 'slug'])->getErrors();
        $this->assertCount(0, $validatorErrorList);
    }

    public function testMethodSlugFail(): void
    {
        $this->assertCount(
            1,
            $this->makeValidator(['slugy' => 'demo-demo-'])->slug(['name' => 'slugy'])->getErrors()
        );
        $this->assertCount(
            1,
            $this->makeValidator(['slugy' => 'Demo-demo'])->slug(['name' => 'slugy'])->getErrors()
        );
        $this->assertCount(
            1,
            $this->makeValidator(['slugy' => 'démo-demo'])->slug(['name' => 'slugy'])->getErrors()
        );
        $this->assertCount(
            1,
            $this->makeValidator(['slugy' => 'demo_demo'])->slug(['name' => 'slugy'])->getErrors()
        );
    }

    public function testMethodSlugIfKeyNotExist(): void
    {
        $validatorErrorList = $this->makeValidator([])->slug(['name' => 'slug'])->getErrors();
        $this->assertCount(0, $validatorErrorList);
    }

    public function testMethodNotEmptyFail(): void
    {
        $validatorErrorList = $this->makeValidator(['content' => ''])->notEmpty(['name' => 'content'])->getErrors();
        $this->assertCount(1, $validatorErrorList);
    }

    public function testMinLengthFail(): void
    {
        $minError = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'min' => 10])
            ->getErrors();
        $this->assertCount(1, $minError);
    }

    public function testMinLengthSuccess(): void
    {
        $minError = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'min' => 3])
            ->getErrors();
        $this->assertCount(0, $minError);
    }

    public function testBetweenLengthFail(): void
    {
        $betweenError = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'min' => 3, 'max' => 8])
            ->getErrors();
        $this->assertCount(1, $betweenError);
    }

    public function testBetweenLengthSuccess(): void
    {
        $betweenError = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'min' => 3, 'max' => 9])
            ->getErrors();
        $this->assertCount(0, $betweenError);
    }

    public function testMaxLengthFailAndMessage(): void
    {
        $constraintList = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'max' => 3])
            ->getErrors();

        $this->assertCount(1, $constraintList);

        foreach ($constraintList as $constraint) {
            $this->assertEquals(
                'Le champs content doit contenir moins de 3 caractrères',
                $constraint->getMessage()
            );
        }
    }

    public function testMaxLengthSuccess(): void
    {
        $maxError = $this->makeValidator($this->getDataForMethodLength())
            ->length(['name' => 'content', 'max' => 9])
            ->getErrors();
        $this->assertCount(0, $maxError);
    }

    public function testDateTime(): void
    {
        $date = ['date' => '2012-12-12 13:12:11'];
        $date2 = ['date' => '2012-12-12 00:00:00'];
        $date3 = ['date' => '2012-21-12'];
        $date4 = ['date' => '2013-02-29 13:12:11'];

        $this->assertCount(0, $this->makeValidator($date)->dateTime(['name' => 'date'])->getErrors());
        $this->assertCount(0, $this->makeValidator($date2)->dateTime(['name' => 'date'])->getErrors());
        $this->assertCount(1, $this->makeValidator($date3)->dateTime(['name' => 'date'])->getErrors());
        $this->assertCount(1, $this->makeValidator($date4)->dateTime(['name' => 'date'])->getErrors());
    }

    public function testExist()
    {
        $pdo = $this->getPdo();
        $this->migrate($pdo);
        $this->seed($pdo);

        $validData = ['category' => 1];
        $invalidData = ['category' => 4578];

        $params = [
            'name' => 'category',
            'table' => 'categories',
            'pdo' => $pdo
        ];

        $this->assertCount(0, $this->makeValidator($validData)->exist($params)->getErrors());
        $this->assertCount(1, $this->makeValidator($invalidData)->exist($params)->getErrors());

    }

    private function makeValidator(array $data): Validator
    {
        return new Validator($data);
    }

    private function getDataForMethodLength(): array
    {
        return ['content' => '123456789'];
    }
}
