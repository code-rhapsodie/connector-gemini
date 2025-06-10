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
            'gemini-2.5-flash-preview-05-20' => 'Gemini 2.5 (Preview)',
            'gemini-2.0-flash' => 'Gemini 2.0',
            'gemini-1.5-flash' => 'Gemini 1.5',
        ], 'text_to_text', 'models');
        $this->assertParameter('gemini-2.0-flash', 'text_to_text', 'default_model');
        $this->assertParameter(4096, 'text_to_text', 'default_max_tokens');
        $this->assertParameter(1.0, 'text_to_text', 'default_temperature');

        $this->assertParameter([
            'gemini-2.5-flash-preview-05-20' => 'Gemini 2.5 (Preview)',
            'gemini-2.0-flash' => 'Gemini 2.0',
            'gemini-1.5-flash' => 'Gemini 1.5',
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
                    'gpt-3.5-turbo' => 'GPT-3.5-turbo',
                ],
                'default_model' => 'gpt-3.5-turbo',
                'default_max_tokens' => 1000,
                'default_temperature' => 0.5,
            ],
            'image_to_text' => [
                'models' => [
                    'gpt-4o-mini' => 'GPT-4o mini',
                    'gpt-4-turbo' => 'GPT-4 Turbo',
                ],
                'default_model' => 'gpt-4o-mini',
                'default_max_tokens' => 2048,
                'default_temperature' => 2.0,
            ],
        ]);

        $this->assertParameter([
            'gpt-3.5-turbo' => 'GPT-3.5-turbo',
        ], 'text_to_text', 'models');
        $this->assertParameter('gpt-3.5-turbo', 'text_to_text', 'default_model');
        $this->assertParameter(1000, 'text_to_text', 'default_max_tokens');
        $this->assertParameter(0.5, 'text_to_text', 'default_temperature');

        $this->assertParameter([
            'gpt-4o-mini' => 'GPT-4o mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
        ], 'image_to_text', 'models');
        $this->assertParameter('gpt-4o-mini', 'image_to_text', 'default_model');
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
