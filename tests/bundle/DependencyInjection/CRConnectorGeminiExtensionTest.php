<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\Bundle\ConnectorGemini\DependencyInjection;

use CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection\CRConnectorGeminiExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;

final class CRConnectorGeminiExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new CRConnectorGeminiExtension(),
        ];
    }

    public function testDefaultConfiguration(): void
    {
        $this->load();

        $this->assertParameter([
            'gemini-2.5' => 'Gemini 2.5',
            'gemini-2.0-flash' => 'Gemini 2.0',
        ], 'text_to_text', 'models');
        $this->assertParameter('gemini-2.0-flash', 'text_to_text', 'default_model');
        $this->assertParameter(4096, 'text_to_text', 'default_max_tokens');
        $this->assertParameter(1.0, 'text_to_text', 'default_temperature');

        $this->assertParameter([
            'gemini-2.5-flash' => 'Gemini 2.5',
            'gemini-2.0-flash' => 'Gemini 2.0',
        ], 'image_to_text', 'models');
        $this->assertParameter('gemini-2.0-flash', 'image_to_text', 'default_model');
        $this->assertParameter(4096, 'image_to_text', 'default_max_tokens');
        $this->assertParameter(1.0, 'image_to_text', 'default_temperature');
    }

    public function testCustomConfiguration(): void
    {
        $this->load([
            'text_to_text' => [
                'models' => [
                    'gemini-2.0-flash' => 'Gemini 2.0',
                ],
                'default_model' => 'gemini-2.0-flash',
                'default_max_tokens' => 1000,
                'default_temperature' => 0.5,
            ],
            'image_to_text' => [
                'models' => [
                    'gemini-2.5-flash' => 'Gemini 2.5',
                    'gemini-2.0-flash' => 'Gemini 2.0',
                ],
                'default_model' => 'gemini-2.0-flash',
                'default_max_tokens' => 2048,
                'default_temperature' => 2.0,
            ],
        ]);

        $this->assertParameter([
            'gemini-2.0-flash' => 'Gemini 2.0',
        ], 'text_to_text', 'models');
        $this->assertParameter('gemini-2.0-flash', 'text_to_text', 'default_model');
        $this->assertParameter(1000, 'text_to_text', 'default_max_tokens');
        $this->assertParameter(0.5, 'text_to_text', 'default_temperature');

        $this->assertParameter([
            'gemini-2.5-flash' => 'Gemini 2.5',
            'gemini-2.0-flash' => 'Gemini 2.0',
        ], 'image_to_text', 'models');
        $this->assertParameter('gemini-2.0-flash', 'image_to_text', 'default_model');
        $this->assertParameter(2048, 'image_to_text', 'default_max_tokens');
        $this->assertParameter(2.0, 'image_to_text', 'default_temperature');
    }

    /**
     * @param mixed $expectedValue
     */
    private function assertParameter($expectedValue, string $actionType, string $name): void
    {
        $key = sprintf('cr.connector_gemini.%s.%s', $actionType, $name);

        self::assertEquals($expectedValue, $this->container->getParameter($key));
    }
}
