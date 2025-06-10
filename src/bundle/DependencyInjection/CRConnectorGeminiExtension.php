<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Yaml\Yaml;

final class CRConnectorGeminiExtension extends Extension implements PrependExtensionInterface
{
    public const EXTENSION_NAME = 'cr_connector_gemini';

    public function getAlias(): string
    {
        return self::EXTENSION_NAME;
    }

    /**
     * @param array<string, mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $mergedConfig = $this->processConfiguration(new Configuration(), $configs);
        foreach ($mergedConfig as $actionType => $actionTypeConfig) {
            foreach ($actionTypeConfig as $key => $value) {
                $container->setParameter(sprintf('cr.connector_gemini.%s.%s', $actionType, $key), $value);
            }
        }

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.yaml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDefaultConfiguration($container);
        $this->prependJMSTranslation($container);
    }

    private function prependDefaultConfiguration(ContainerBuilder $container): void
    {
        $configFile = __DIR__ . '/../Resources/config/prepend.yaml';

        $container->addResource(new FileResource($configFile));

        $configs = Yaml::parseFile($configFile, Yaml::PARSE_CONSTANT) ?? [];
        foreach ($configs as $name => $config) {
            $container->prependExtensionConfig($name, $config);
        }
    }

    private function prependJMSTranslation(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ibexa_connector_gemini' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'excluded_dirs' => [],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                ],
            ],
        ]);
    }
}
