<?php

declare(strict_types=1);

namespace CodeRhapsodie\Contracts\ConnectorGemini;


use Gemini\Client;

interface ClientProviderInterface
{
    public function getClient(): Client;
}
