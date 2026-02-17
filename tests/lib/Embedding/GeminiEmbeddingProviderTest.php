<?php

declare(strict_types=1);

namespace CodeRhapsodie\Tests\ConnectorGemini\Embedding;

use CodeRhapsodie\ConnectorGemini\Embedding\GeminiEmbeddingProvider;
use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Contracts\ClientContract;
use Gemini\Contracts\Resources\EmbeddingModalContract;
use Gemini\Responses\GenerativeModel\EmbedContentResponse;
use Ibexa\Contracts\Core\Search\Embedding\EmbeddingConfigurationInterface;
use PHPUnit\Framework\TestCase;

final class GeminiEmbeddingProviderTest extends TestCase
{
    private const CONTENT = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
    private const DEFAULT_MODEL_NAME = 'gemini-embedding-001';
    private const EXPECTED_EMBEDDING = [-0.0065183877, -0.0015175042, 0.017056655];

    public function testGetEmbeddingUsesDefaultModel(): void
    {
        $fakeResponse = $this->createFakeResponse(self::EXPECTED_EMBEDDING);

        $clientProviderMock = $this->getClientProviderMock($fakeResponse);

        $configMock = $this->createMock(EmbeddingConfigurationInterface::class);
        $configMock
            ->method('getDefaultModel')
            ->willReturn(['name' => self::DEFAULT_MODEL_NAME]);

        $provider = new GeminiEmbeddingProvider($clientProviderMock, $configMock);

        $result = $provider->getEmbedding(self::CONTENT);

        self::assertSame(self::EXPECTED_EMBEDDING, $result);
    }

    public function testGetEmbeddingPassesMaxTokens(): void
    {
        $fakeResponse = $this->createFakeResponse(self::EXPECTED_EMBEDDING);

        $clientProviderMock = $this->getClientProviderMock($fakeResponse, self::DEFAULT_MODEL_NAME, 256);

        $configMock = $this->createMock(EmbeddingConfigurationInterface::class);
        $configMock
            ->method('getDefaultModel')
            ->willReturn(['name' => self::DEFAULT_MODEL_NAME]);

        $provider = new GeminiEmbeddingProvider($clientProviderMock, $configMock);

        $result = $provider->getEmbedding(self::CONTENT, null, 256);

        self::assertSame(self::EXPECTED_EMBEDDING, $result);
    }

    public function testGetEmbeddingUsesOverrideModel(): void
    {
        $overrideModelName = 'custom-model-123';
        $fakeResponse = $this->createFakeResponse(self::EXPECTED_EMBEDDING);

        $clientProviderMock = $this->getClientProviderMock($fakeResponse, $overrideModelName);

        $configMock = $this->createMock(EmbeddingConfigurationInterface::class);
        $configMock
            ->expects(self::never())
            ->method('getDefaultModel');

        $provider = new GeminiEmbeddingProvider($clientProviderMock, $configMock);
        $result = $provider->getEmbedding(self::CONTENT, $overrideModelName);

        self::assertSame(self::EXPECTED_EMBEDDING, $result);
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function invalidEmbeddingResponsesProvider(): iterable
    {
        yield 'missing data key' => [
            (string)json_encode([
                // 'data' is missing
            ]),
        ];
        yield 'data not an array' => [
            (string)json_encode([
                'data' => null,
            ]),
        ];
        yield 'missing data[0]' => [
            (string)json_encode([
                'data' => [],
            ]),
        ];
        yield 'missing embedding in data[0]' => [
            (string)json_encode([
                'data' => [
                    [], // data[0] exists, but no 'embedding'
                ],
            ]),
        ];
    }

    /**
     * @param array<float> $embedding
     */
    private function createFakeResponse(array $embedding): string
    {
        return json_encode(['embedding' => ['values' => $embedding]], JSON_THROW_ON_ERROR);
    }

    private function getClientProviderMock(string $response, string $model = self::DEFAULT_MODEL_NAME, ?int $maxTokens = null): ClientProviderInterface
    {
        $embeddingModal = $this->createMock(EmbeddingModalContract::class);

        $embeddingModal->method('embedContent')
            ->with(self::CONTENT)
            ->willReturn(EmbedContentResponse::from(json_decode($response, true)));

        $clientMock = $this->createMock(ClientContract::class);
        $clientMock
            ->method('embeddingModel')
            ->with($model)
            ->willReturn($embeddingModal);

        $clientProviderMock = $this->createMock(ClientProviderInterface::class);
        $clientProviderMock
            ->method('getClient')
            ->willReturn($clientMock);

        return $clientProviderMock;
    }
}
