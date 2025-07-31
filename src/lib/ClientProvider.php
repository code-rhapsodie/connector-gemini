<?php


declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini;
use Gemini\Client;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;

final readonly class ClientProvider implements ClientProviderInterface
{
    public function __construct(private ConfigResolverInterface $configResolver)
    {
    }

    public function getClient(): Client
    {
        return Gemini::client($this->configResolver->getParameter('connector_gemini.gemini.api_key'));
    }
}
