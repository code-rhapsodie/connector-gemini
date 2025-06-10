<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection\Configuration\SiteAccessAware;

use Ibexa\Bundle\Core\DependencyInjection\Configuration\AbstractParser;
use Ibexa\Bundle\Core\DependencyInjection\Configuration\SiteAccessAware\ContextualizerInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

final class ConnectorGeminiParser extends AbstractParser
{
    /**
     * @param array<mixed> $scopeSettings
     */
    public function mapConfig(array &$scopeSettings, $currentScope, ContextualizerInterface $contextualizer): void
    {
        if (!isset($scopeSettings['connector_gemini'])) {
            return;
        }

        $settings = $scopeSettings['connector_gemini'];

        $this->addApiKeyParameters($settings, $currentScope, $contextualizer);
    }

    public function addSemanticConfig(NodeBuilder $nodeBuilder): void
    {
        $rootConnectorAiNode = $nodeBuilder->arrayNode('connector_gemini');
        $rootConnectorAiNode->append($this->addGeminiConfiguration());
    }

    private function addGeminiConfiguration(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder('gemini');
        $node = $treeBuilder->getRootNode();

        $node
            ->children()
                ->scalarNode('api_key')
                    ->isRequired()
                ->end()
            ->end();

        return $node;
    }

    /**
     * @param array<string, mixed> $settings
     */
    private function addApiKeyParameters(
        array $settings,
        string $currentScope,
        ContextualizerInterface $contextualizer
    ): void {
        $names = [
            'api_key',
        ];

        foreach ($names as $name) {
            if (isset($settings['gemini'][$name])) {
                $contextualizer->setContextualParameter(
                    'connector_gemini.gemini.' . $name,
                    $currentScope,
                    $settings['gemini'][$name]
                );
            }
        }
    }
}
