<?php


declare(strict_types=1);

namespace CodeRhapsodie\ConnectorGemini\ActionHandler;

use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Client;
use Gemini\Contracts\ClientContract;
use Ibexa\Contracts\ConnectorAi\Action\ActionHandlerInterface;
use Ibexa\Contracts\ConnectorAi\Action\LLMBaseActionTypeInterface;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeInterface;
use Ibexa\Contracts\ConnectorAi\ActionType\ActionTypeRegistryInterface;
use Ibexa\Contracts\ConnectorAi\DataType;
use Ibexa\Contracts\Core\Exception\InvalidArgumentException;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Rest\Server\Exceptions\BadRequestException;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractActionHandler implements ActionHandlerInterface
{
    protected ClientContract $client;

    protected string $defaultModel = 'gemini-2.0-flash';

    protected int $defaultMaxTokens = 4096;

    protected float $defaultTemperature = 1.0;

    public function __construct(
        ClientProviderInterface $clientProvider,
        protected ActionTypeRegistryInterface $actionTypeRegistry,
        private readonly LanguageService $languageService,
        private readonly LanguageResolver $languageResolver
    ) {
        $this->client = $clientProvider->getClient();
    }

    protected function buildPrompt(ActionInterface $action): string
    {
        $actionTypeIdentifier = $action->getActionTypeIdentifier();
        if (!$this->actionTypeRegistry->hasActionType($actionTypeIdentifier)) {
            throw new InvalidArgumentException(
                'actionTypeIdentifier',
                sprintf("Could not find %s for \'%s\' action type", ActionTypeInterface::class, $actionTypeIdentifier)
            );
        }

        $actionType = $this->actionTypeRegistry->getActionType($actionTypeIdentifier);
        if (!$actionType instanceof LLMBaseActionTypeInterface) {
            throw new InvalidArgumentException(
                'actionType',
                'expected LLMBaseActionTypeInterface type, ' . get_debug_type($actionType) . ' given.'
            );
        }

        return $actionType->getBasePrompt();
    }

    /**
     * @return mixed|null
     */
    protected function getOption(string $key, ActionInterface $action)
    {
        $option = null;

        if ($action->getRuntimeContext() && $action->getRuntimeContext()->get($key) !== null) {
            $option = $action->getRuntimeContext()->get($key);
        } elseif ($action->getActionContext() && $action->getActionContext()->getActionTypeOptions()->get($key)) {
            $option = $action->getActionContext()->getActionTypeOptions()->get($key);
        }

        return $option;
    }

    protected function resolveModel(ActionInterface $action): string
    {
        if ($action->hasActionContext()) {
            return $action->getActionContext()->getActionHandlerOptions()->get('model', $this->defaultModel);
        }

        return $this->defaultModel;
    }

    protected function resolveMaxTokens(ActionInterface $action): int
    {
        if ($action->hasActionContext()) {
            return (int)$action->getActionContext()->getActionHandlerOptions()->get('max_tokens');
        }

        return $this->defaultMaxTokens;
    }

    protected function resolveTemperature(ActionInterface $action): float
    {
        if ($action->hasActionContext()) {
            return (float)$action->getActionContext()->getActionHandlerOptions()->get('temperature');
        }

        return $this->defaultTemperature;
    }

    public function getDefaultModel(): string
    {
        return $this->defaultModel;
    }

    public function setDefaultModel(string $defaultModel): void
    {
        $this->defaultModel = $defaultModel;
    }

    public function getDefaultMaxTokens(): int
    {
        return $this->defaultMaxTokens;
    }

    public function setDefaultMaxTokens(int $defaultMaxTokens): void
    {
        $this->defaultMaxTokens = $defaultMaxTokens;
    }

    public function getDefaultTemperature(): float
    {
        return $this->defaultTemperature;
    }

    public function setDefaultTemperature(float $defaultTemperature): void
    {
        $this->defaultTemperature = $defaultTemperature;
    }

    protected function getLanguage(ActionInterface $action): string
    {
        $languageCode = $this->getOption('languageCode', $action) ?? $this->getDefaultLanguageCode();

        return $this->languageService->loadLanguage($languageCode)->getName();
    }

    private function getDefaultLanguageCode(): string
    {
        $prioritizedLanguages = $this->languageResolver->getPrioritizedLanguages();
        $defaultLanguageCode = reset($prioritizedLanguages);

        if (!$defaultLanguageCode) {
            throw new RuntimeException('Unable to load default language code');
        }

        return $defaultLanguageCode;
    }

    /**
     * @param array<string> $prompts
     *
     * @return array<string>
     */
    protected function addLengthPrompt(ActionInterface $action, array $prompts): array
    {
        $maxLength = $this->getOption('max_length', $action);
        if ($maxLength !== null) {
            $prompts[] = 'Do not exceed text length of ' . $maxLength . ' characters';
        }

        return $prompts;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define('prompt')->allowedTypes('string');
        $resolver->define('action_type_identifier')
            ->required()
            ->default(null)
            ->allowedTypes('string');

        $resolver->define('action_handler_identifier')
            ->required()
            ->default(static::getIdentifier())
            ->allowedTypes('string');

        $resolver->define('languageCode')
            ->required()
            ->default($this->getDefaultLanguageCode())
            ->allowedTypes('string');

        $resolver->define('max_tokens')
            ->required()
            ->default($this->defaultMaxTokens)
            ->allowedTypes('int');

        $resolver->define('model')
            ->required()
            ->default($this->defaultModel)
            ->allowedTypes('string');

        $resolver->define('temperature')
            ->required()
            ->default($this->defaultTemperature)
            ->allowedTypes('numeric');

        $resolver->define('action_input')
            ->allowedTypes(DataType::class);
    }

    /**
     * @return array<mixed>
     */
    protected function resolveOptions(ActionInterface $action): array
    {
        $resolver = new OptionsResolver();

        $this->configureOptions($resolver);

        return $resolver->resolve($action->getAllOptions());
    }

    /**
     * @param string|bool $json
     */
    protected function validateResponse($json): void
    {
        if (!is_string($json)) {
            throw new RuntimeException('The response should be a string, but a boolean was returned.');
        }

        $data = $this->decode($json);

        if(isset($data['error'])) {
            throw new BadRequestException($data['error']['message']);
        }
    }

    /**
     * @return array<mixed>
     *
     * @throws \JsonException
     */
    private function decode(string $json): array
    {
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
