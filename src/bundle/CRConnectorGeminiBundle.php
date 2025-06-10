<?php

declare(strict_types=1);

namespace CodeRhapsodie\Bundle\ConnectorGemini;

use CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection\Configuration\SiteAccessAware\ConnectorGeminiParser;
use CodeRhapsodie\Bundle\ConnectorGemini\DependencyInjection\CRConnectorGeminiExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class CRConnectorGeminiBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new CRConnectorGeminiExtension();
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        /** @var \Ibexa\Bundle\Core\DependencyInjection\IbexaCoreExtension $ibexaExtension */
        $ibexaExtension = $container->getExtension('ibexa');
        $ibexaExtension->addConfigParser(new ConnectorGeminiParser());
        $ibexaExtension->addDefaultSettings(__DIR__ . '/Resources/config', ['default_settings.yaml']);
    }
}
