<?php

namespace Rezzza\SymfonyRestApiJson\Tests\Units;

use mageekguy\atoum;

class PayloadValidator extends atoum
{
    public function test_validation_should_be_delegated_to_internal_validator()
    {
        $this
            ->given(
                $validator = $this->mockJsonSchemaValidator(),
                $this->calling($validator)->check = null,
                $schemaStorage = $this->mockJsonSchemaStorage(),
                $this->calling($schemaStorage)->resolveRef = 'resolvedJsonSchema',
                $jsonSchemaTools = $this->mockJsonSchemaTools($validator, $schemaStorage),
                $this->newTestedInstance($jsonSchemaTools)
            )
            ->when(
                $this->testedInstance->validate('"json"', __DIR__.'/../Fixtures/mySchema.json')
            )
            ->then
                ->mock($validator)
                    ->call('check')
                    ->withArguments('json', 'resolvedJsonSchema')
                    ->once()
        ;
    }

    public function test_unknown_json_schema_lead_to_exception()
    {
        $this
            ->given(
                $jsonSchemaTools = $this->mockJsonSchemaTools(),
                $this->newTestedInstance($jsonSchemaTools)
            )
            ->exception(function () {
                $this->testedInstance->validate('"json"', 'hello.json');
            })
                ->isInstanceOf(\UnexpectedValueException::class)
        ;
    }

    public function test_invalid_internal_validation_lead_to_exception()
    {
        $this
            ->given(
                $validator = $this->mockJsonSchemaValidator(),
                $this->calling($validator)->check = null,
                $this->calling($validator)->isValid = false,
                $this->calling($validator)->getErrors = ['error1', 'error2'],
                $schemaStorage = $this->mockJsonSchemaStorage(),
                $this->calling($schemaStorage)->resolveRef = 'resolvedJsonSchema',
                $jsonSchemaTools = $this->mockJsonSchemaTools($validator, $schemaStorage),
                $this->newTestedInstance($jsonSchemaTools)
            )
            ->exception(function () {
                $this->testedInstance->validate('"json"', __DIR__.'/../Fixtures/mySchema.json');
            })
                ->isInstanceOf(\Rezzza\SymfonyRestApiJson\InvalidPayload::class)
                ->phpArray($this->exception->getErrors())
                    ->isEqualTo(['error1', 'error2'])
        ;
    }

    private function mockJsonSchemaValidator()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\JsonSchema\Validator;
    }

    private function mockJsonSchemaStorage()
    {
        $this->mockGenerator->orphanize('__construct');

        return new \mock\JsonSchema\SchemaStorage();
    }

    private function mockJsonSchemaTools($validator = null, $schemaStorage = null)
    {
        $this->mockGenerator->orphanize('__construct');
        $mock = new \mock\Rezzza\SymfonyRestApiJson\JsonSchemaTools;
        $this->calling($mock)->createValidator = $validator;
        $this->calling($mock)->createSchemaStorage = $schemaStorage;

        return $mock;
    }
}
