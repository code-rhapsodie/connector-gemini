<?php

declare(strict_types=1);

namespace CodeRhapsodie\Contracts\ConnectorGemini;

use Gemini\Contracts\ClientContract;

interface ClientProviderInterface
{
    public function getClient(): ClientContract;
}
