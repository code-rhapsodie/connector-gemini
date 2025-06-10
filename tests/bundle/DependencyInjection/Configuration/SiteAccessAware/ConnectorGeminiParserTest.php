<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\Bundle\ConnectorGemini\DependencyInjection\Configuration\SiteAccessAware;

use CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection\Configuration\SiteAccessAware\ConnectorGeminiParser;
use Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension;
use Ibexa\Tests\Bundle\Core\DependencyInjection\Configuration\Parser\AbstractParserTestCase;

final class ConnectorGeminiParserTest extends AbstractParserTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            new IbexaCoreExtension(
                [
                    new ConnectorGeminiParser(),
                ]
            ),
        ];
    }

    public function testEmptyConfiguration(): void
    {
        $this->load($this->buildConfiguration([]));

        $this->assertConfigResolverParameterIsNotSet('gemini.api_key', 'ibexa_demo_site');
    }

    public function testGeminiConfiguration(): void
    {
        $config = $this->buildConfiguration([
            'gemini' => ['api_key' => '1!2@3#'],
        ]);
        $this->load($config);

        $this->assertConfigResolverParameterValue(
            'connector_gemini.gemini.api_key',
            '1!2@3#',
            'ibexa_demo_site'
        );
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, array<mixed>>
     */
    private function buildConfiguration(array $config): array
    {
        return [
            'system' => [
                'ibexa_demo_site' => [
                    'connector_gemini' => $config,
                ],
            ],
        ];
    }

    private function assertConfigResolverParameterIsNotSet(string $parameterName, ?string $scope = null): void
    {
        $chainConfigResolver = $this->getConfigResolver();
        self::assertFalse(
            $chainConfigResolver->hasParameter($parameterName, 'ibexa.site_access.config', $scope),
            sprintf('Parameter "%s" should not exist in scope "%s"', $parameterName, $scope)
        );
    }
}
