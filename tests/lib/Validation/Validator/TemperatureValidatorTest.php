<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\ConnectorGemini\Validation\Validator;

use CodeRhapsodie\ConnectorGemini\Validation\Validator\TemperatureValidator;
use Ibexa\Contracts\ConnectorAi\ActionConfiguration\ActionConfigurationOptions as ActionConfigurationOptionsBag;
use Ibexa\Contracts\ConnectorAi\ActionType\OptionsValidatorError;
use PHPUnit\Framework\TestCase;

final class TemperatureValidatorTest extends TestCase
{
    private const MESSAGE = 'Temperature must be a number between 0 and 2.';

    private TemperatureValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new TemperatureValidator();
    }

    public function testValidLength(): void
    {
        $options = new ActionConfigurationOptionsBag(['temperature' => 1]);

        self::assertEmpty($this->validator->validateOptions($options));
    }

    /**
     * @param array<string, mixed> $options
     *
     * @dataProvider dataProviderForTestInvalidValues
     */
    public function testInvalidLength(array $options): void
    {
        $options = new ActionConfigurationOptionsBag($options);

        self::assertEquals(
            [new OptionsValidatorError('[temperature]', self::MESSAGE)],
            $this->validator->validateOptions($options)
        );
    }

    /**
     * @return iterable<string, array{length?: int|null}>
     */
    public function dataProviderForTestInvalidValues(): iterable
    {
        yield 'no option' => [[]];
        yield 'negative' => [['temperature' => -1]];
        yield 'value too low' => [['temperature' => -0.1]];
        yield 'value too high' => [['temperature' => 2.1]];
        yield 'null' => [['temperature' => null]];
    }
}
