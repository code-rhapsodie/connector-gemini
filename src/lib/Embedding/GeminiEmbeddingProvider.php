<?php

declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini\Embedding;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Ibexa\Contracts\Core\Search\Embedding\EmbeddingConfigurationInterface;
use Ibexa\Contracts\Core\Search\Embedding\EmbeddingProviderInterface;

final class GeminiEmbeddingProvider implements EmbeddingProviderInterface
{
    private ClientProviderInterface $clientProvider;

    private EmbeddingConfigurationInterface $embeddingConfiguration;

    public function __construct(
        ClientProviderInterface $clientProvider,
        EmbeddingConfigurationInterface $embeddingConfiguration
    ) {
        $this->clientProvider = $clientProvider;
        $this->embeddingConfiguration = $embeddingConfiguration;
    }

    /**
     * @return float[]
     *
     * @throws \JsonException
     */
    public function getEmbedding(string $content, ?string $model = null, ?int $maxTokens = null): array
    {
        return $this->clientProvider->getClient()->embeddingModel($model ?? $this->embeddingConfiguration->getDefaultModel()['name'])->embedContent($content)->embedding->values;
    }
}
