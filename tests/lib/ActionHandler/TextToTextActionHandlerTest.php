<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\ConnectorGemini\ActionHandler;

use CodeRhapsodie\ConnectorGemini\ActionHandler\TextToTextActionHandler;
use CodeRhapsodie\Contracts\ConnectorGemini\ClientProviderInterface;
use Gemini\Contracts\ClientContract;
use Gemini\Responses\GenerativeModel\GenerateContentResponse;
use Gemini\Testing\ClientFake;
use Ibexa\AdminUi\Event\Options;
use Ibexa\ConnectorAi\ActionTypeRegistry;
use Ibexa\ConnectorAi\Prompt\ActionInputTextPrompt;
use Ibexa\ConnectorAi\Prompt\AdditionalPrompt;
use Ibexa\ConnectorAi\Prompt\BasePrompt;
use Ibexa\ConnectorAi\Prompt\LanguagePrompt;
use Ibexa\ConnectorAi\PromptResolver;
use Ibexa\ConnectorAi\RefineTextActionType;
use Ibexa\Contracts\ConnectorAi\Action\Action;
use Ibexa\Contracts\ConnectorAi\Action\ActionContext;
use Ibexa\Contracts\ConnectorAi\Action\ActionFactoryInterface;
use Ibexa\Contracts\ConnectorAi\Action\DataType\Text;
use Ibexa\Contracts\ConnectorAi\Action\RefineTextAction;
use Ibexa\Contracts\ConnectorAi\Action\Response\TextResponse;
use Ibexa\Contracts\ConnectorAi\Action\TextToText\Action as TextToTextAction;
use Ibexa\Contracts\ConnectorAi\ActionInterface;
use Ibexa\Contracts\ConnectorAi\Prompt\PromptFactory;
use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TextToTextActionHandlerTest extends AbstractActionHandlerTest
{
    private TextToTextActionHandler $handler;

    private ClientContract $client;

    protected function setUp(): void
    {
        $actionTypeRegistry = new ActionTypeRegistry(
            [
                'refine_text' => new RefineTextActionType(
                    $this->createMock(ActionFactoryInterface::class),
                    $this->createMock(TranslatorInterface::class),
                    []
                ),
            ]
        );

        $this->client = new ClientFake([
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
            ->willReturn($this->client);

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
                        new AdditionalPrompt(),
                        new ActionInputTextPrompt(),
                    ],
                    'gemini-text-to-text'
                ),
            ],
        );

        $this->handler = new TextToTextActionHandler(
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
            $this->createMock(TextToTextAction::class),
            true,
        ];

        yield 'Unsupported' => [
            $this->createMock(Action::class),
            false,
        ];
    }

    public function testHandle(): void
    {
        $action = new RefineTextAction(new Text(['some text']));
        $action->setActionContext(new ActionContext(
            new Options(),
            new Options(),
            new Options([
                'model' => 'gpt-4-turbo',
                'prompt' => 'Make it sound funny',
                'max_tokens' => 4096,
                'temperature' => 1,
            ]),
        ));
        $actionResponse = $this->handler->handle($action);
        self::assertInstanceOf(TextResponse::class, $actionResponse);

        self::assertEquals('foo', $actionResponse->getOutput()->getText());
    }
}
