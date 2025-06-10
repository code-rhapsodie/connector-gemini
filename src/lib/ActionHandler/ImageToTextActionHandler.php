<?php

declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini\ActionHandler;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Data\Blob;
use Gemini\Data\GenerationConfig;
use Gemini\Enums\MimeType;
use Ibexa\Contracts\ConnectorAi\Action\DataType\Text;
use Ibexa\Contracts\ConnectorAi\Action\ImageToText\Action as ImageToTextAction;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\ActionResponseInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeRegistryInterface;
use Ibexa\Contracts\ConnectorAi\PromptResolverInterface;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ImageToTextActionHandler extends AbstractActionHandler
{
    use ResponseFormatter;

    public const INDEX = 'gemini-image-to-text';

    private PromptResolverInterface $promptResolver;

    public function __construct(
        ClientProviderInterface $clientProvider,
        ActionTypeRegistryInterface $actionTypeRegistry,
        LanguageService $languageService,
        LanguageResolver $languageResolver,
        PromptResolverInterface $promptResolver
    ) {
        parent::__construct($clientProvider, $actionTypeRegistry, $languageService, $languageResolver);

        $this->promptResolver = $promptResolver;
    }

    public function supports(ActionInterface $action): bool
    {
        return $action instanceof ImageToTextAction;
    }

    /**
     * @param \Ibexa\Contracts\ConnectorAi\Action\ImageToText\Action $action
     */
    public function handle(ActionInterface $action, array $context = []): ActionResponseInterface
    {
        $options = $this->resolveOptions($action);

        preg_match('#data:(image/[a-z-+]+);base64,([a-zA-Z0-9,+/]+={0,2})#', $action->getInput()->getBase64(), $matches);

        if (\count($matches) !== 3) {
           throw new \Exception('Invalid image data');
        }

        $data = $this->client->generativeModel($options['model'])
            ->withGenerationConfig(new GenerationConfig(maxOutputTokens: $options['max_tokens'], temperature: $options['temperature']))
            ->generateContent([
                $this->promptResolver->getPrompt($options),
                new Blob(mimeType: MimeType::from($matches[1]), data: $matches[2]),
            ])->toArray();

        return new TextResponse(new Text($this->format($data)));
    }

    public static function getIdentifier(): string
    {
        return self::INDEX;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->define('max_length')->allowedTypes('int');
    }
}
