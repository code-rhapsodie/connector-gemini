<?php


declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini;
use Gemini\Client;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final class ClientProvider implements ClientProviderInterface
{
    private ConfigResolverInterface $configResolver;

    public function __construct(
        ConfigResolverInterface $configResolver
    ) {
        $this->configResolver = $configResolver;
    }

    public function getClient(): Client
    {
        return Gemini::client($this->configResolver->getParameter('connector_gemini.gemini.api_key'));
    }
}
