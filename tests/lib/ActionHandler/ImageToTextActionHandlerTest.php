<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\ConnectorGemini\ActionHandler;

use CodeRhapsodie\ConnectorGemini\ActionHandler\ImageToTextActionHandler;
use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Contracts\ClientContract;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Gemini\Testing\ClientFake;
use Ibexa\ConnectorAi\ActionContextFactory;
use Ibexa\ConnectorAi\ActionTypeRegistry;
use Ibexa\ConnectorAi\GenerateAltTextActionType;
use Ibexa\ConnectorAi\Prompt\AdditionalPrompt;
use Ibexa\ConnectorAi\Prompt\BasePrompt;
use Ibexa\ConnectorAi\Prompt\LanguagePrompt;
use Ibexa\ConnectorAi\Prompt\LengthPrompt;
use Ibexa\ConnectorAi\PromptResolver;
use Ibexa\Contracts\ConnectorAi\Action\Action;
use Ibexa\Contracts\ConnectorAi\Action\ActionFactoryInterface;
use Ibexa\Contracts\ConnectorAi\Action\DataType\Image;
use Ibexa\Contracts\ConnectorAi\Action\GenerateAltTextAction;
use Ibexa\Contracts\ConnectorAi\Action\ImageToText\Action as ImageToTextAction;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\ActionConfiguration\ActionConfigurationOptions;
use Ibexa\Contracts\ConnectorAi\ActionConfigurationInterface;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\Prompt\PromptFactory;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ImageToTextActionHandlerTest extends AbstractActionHandlerTest
{
    private ImageToTextActionHandler $handler;

    protected function setUp(): void
    {
        $actionTypeRegistry = new ActionTypeRegistry(
            [
                'generate_alt_text' => new GenerateAltTextActionType(
                    $this->createMock(ActionFactoryInterface::class),
                    $this->createMock(TranslatorInterface::class),
                    []
                ),
            ]
        );

        $client = new ClientFake([
            GenerateContentResponse::fake([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => 'foo',
                                ],
                            ],
                        ],
                    ],
                ],
            ]),
        ]);

        $clientProvider = $this->createMock(ClientProviderInterface::class);
        $clientProvider
            ->method('getClient')
            ->willReturn($client);

        $languageService = $this->createMock(LanguageService::class);
        $languageService
            ->method('loadLanguage')
            ->with('eng-GB')
            ->willReturn(new Language(['name' => 'english']));

        $languageResolver = $this->createMock(LanguageResolver::class);

        $promptResolver = new PromptResolver(
            [
                new PromptFactory(
                    [
                        new BasePrompt($actionTypeRegistry),
                        new LanguagePrompt($languageService, $languageResolver),
                        new LengthPrompt(),
                        new AdditionalPrompt(),
                    ],
                    'gemini-image-to-text'
                ),
            ],
        );

        $this->handler = new ImageToTextActionHandler(
            $clientProvider,
            $actionTypeRegistry,
            $this->getLanguageServiceMock(),
            $this->getLanguageResolverMock(),
            $promptResolver
        );
    }

    /**
     * @dataProvider provideDataForTestSupports
     */
    public function testSupports(ActionInterface $action, bool $expectedResult): void
    {
        self::assertSame($this->handler->supports($action), $expectedResult);
    }

    /**
     * @return iterable<string, array{\Ibexa\Contracts\ConnectorAi\ActionInterface, bool}>
     */
    public function provideDataForTestSupports(): iterable
    {
        yield 'Supported' => [
            $this->createMock(ImageToTextAction::class),
            true,
        ];

        yield 'Unsupported' => [
            $this->createMock(Action::class),
            false,
        ];
    }

    public function testHandle(): void
    {
        $action = new GenerateAltTextAction(new Image(['data:image/jpeg;base64,R0lGODdhAQABAPAAAP8AAAAAACwAAAAAAQABAAACAkQBADs=']));
        $actionResponse = $this->handler->handle($action);
        self::assertInstanceOf(TextResponse::class, $actionResponse);

        self::assertEquals('foo', $actionResponse->getOutput()->getText());
    }

    /**
     * @return array<string, mixed>
     */
    private function getOpts(
        string $text = 'Generate alt text for given image. You reply in english language only',
        string $model = 'gpt-4-turbo',
        int $maxTokens = 4096,
        int $temperature = 1
    ): array {
        return [
            'model' => $model,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $text,
                        ],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => 'data:image/jpeg;base64,R0lGODdhAQABAPAAAP8AAAAAACwAAAAAAQABAAACAkQBADs=',
                                'detail' => 'low',
                            ],
                        ],
                    ],
                ],
            ],
            'max_tokens' => $maxTokens,
            'temperature' => $temperature,
        ];
    }

    public function testHandleWithActionConfiguration(): void
    {
        $opts = $this->getOpts(
            'Generate alt text for given image. You reply in english language only. Do not exceed text length of 100 characters. Make it sound cute',
            'gpt-3.5-turbo',
            2048,
            2
        );

        $actionConfiguration = $this->getActionConfiguration();
        $action = new GenerateAltTextAction(new Image(['data:image/jpeg;base64,R0lGODdhAQABAPAAAP8AAAAAACwAAAAAAQABAAACAkQBADs=']));
        $actionContext = ActionContextFactory::create(
            new ActionConfigurationOptions([]),
            $actionConfiguration
        );
        $action->setActionContext($actionContext);
        $actionResponse = $this->handler->handle($action);
        self::assertInstanceOf(TextResponse::class, $actionResponse);
        self::assertEquals('foo', $actionResponse->getOutput()->getText());
    }

    private function getActionConfiguration(): ActionConfigurationInterface
    {
        $actionConfiguration = $this->createMock(ActionConfigurationInterface::class);
        $actionConfiguration
            ->method('getActionTypeOptions')
            ->willReturn(
                new ActionConfigurationOptions(
                    [
                        'max_length' => 100,
                    ]
                )
            );
        $actionConfiguration
            ->method('getActionHandlerOptions')
            ->willReturn(
                new ActionConfigurationOptions(
                    [
                        'model' => 'gpt-3.5-turbo',
                        'max_tokens' => 2048,
                        'temperature' => 2,
                        'prompt' => 'Make it sound cute',
                    ]
                )
            );

        return $actionConfiguration;
    }
}
