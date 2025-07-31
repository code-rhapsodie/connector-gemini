<?php

declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini\ActionHandler;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Data\GenerationConfig;
use Ibexa\Contracts\ConnectorAi\Action\DataType\Text;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\Action\TextToText\Action as TextToTextAction;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\ActionResponseInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeRegistryInterface;
use Ibexa\Contracts\ConnectorAi\PromptResolverInterface;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;

final class TextToTextActionHandler extends AbstractActionHandler
{
    use ResponseFormatter;

    public const string INDEX = 'gemini-text-to-text';

    public function __construct(
        ClientProviderInterface $clientProvider,
        ActionTypeRegistryInterface $actionTypeRegistry,
        LanguageService $languageService,
        LanguageResolver $languageResolver,
        private PromptResolverInterface $promptResolver
    ) {
        parent::__construct($clientProvider, $actionTypeRegistry, $languageService, $languageResolver);
    }

    public function supports(ActionInterface $action): bool
    {
        return $action instanceof TextToTextAction;
    }

    /**
     * @param \Ibexa\Contracts\ConnectorAi\Action\TextToText\Action $action
     */
    public function handle(ActionInterface $action, array $context = []): ActionResponseInterface
    {
        $options = $this->resolveOptions($action);

        $data = $this->client->generativeModel($options['model'])
            ->withGenerationConfig(new GenerationConfig(maxOutputTokens: $options['max_tokens'], temperature: $options['temperature']))
            ->generateContent([
                $this->promptResolver->getPrompt($options),
            ])->toArray();

        return new TextResponse(new Text($this->format($data)));
    }

    public static function getIdentifier(): string
    {
        return self::INDEX;
    }
}
