<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder(CRConnectorGeminiExtension::EXTENSION_NAME);

        $rootNode = $builder->getRootNode();
        $rootNode
            ->children()
                ->append($this->getActionConfigurationNode('text_to_text', [
                    'gemini-2.5-flash' => 'Gemini 2.5',
                    'gemini-2.0-flash' => 'Gemini 2.0',
                    'gemini-1.5-flash' => 'Gemini 1.5',
                ]))
                ->append($this->getActionConfigurationNode('image_to_text', [
                    'gemini-2.5-flash' => 'Gemini 2.5',
                    'gemini-2.0-flash' => 'Gemini 2.0',
                    'gemini-1.5-flash' => 'Gemini 1.5',
                ]))
            ->end();

        return $builder;
    }

    /**
     * @param array<string, string> $models
     */
    private function getActionConfigurationNode(string $name, array $models): NodeDefinition
    {
        $builder = new TreeBuilder($name);

        $rootNode = $builder->getRootNode();
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->variableNode('models')
                    ->isRequired()
                    ->defaultValue($models)
                ->end()
                ->scalarNode('default_model')
                    ->isRequired()
                    ->defaultValue('gemini-2.0-flash')
                    ->info('Default model identifier.')
                ->end()
                ->integerNode('default_max_tokens')
                    ->isRequired()
                    ->defaultValue(4096)
                    ->info('Default maximum number of tokens that can be generated in the chat completion.')
                ->end()
                ->floatNode('default_temperature')
                    ->isRequired()
                    ->defaultValue(1.0)
                    ->min(0.0)
                    ->max(2.0)
                    ->info('Default sampling temperature to use, between 0 and 2. Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.')
                ->end()
            ->end();

        return $rootNode;
    }
}
