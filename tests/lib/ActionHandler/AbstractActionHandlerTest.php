<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\ConnectorGemini\ActionHandler;

use Ibexa\Contracts\Core\Repository\LanguageResolver;
use Ibexa\Contracts\Core\Repository\LanguageService;
use Ibexa\Contracts\Core\Repository\Values\Content\Language;
use PHPUnit\Framework\TestCase;

abstract class AbstractActionHandlerTest extends TestCase
{
    protected function getLanguage(string $name): Language
    {
        $language = $this->createMock(Language::class);
        $language
            ->method('getName')
            ->willReturn($name);

        return $language;
    }

    protected function getLanguageServiceMock(): LanguageService
    {
        $languageService = $this->createMock(LanguageService::class);
        $languageService
            ->method('loadLanguage')
            ->willReturnMap([
                ['eng-GB', $this->getLanguage('english')],
                ['pol-PL', $this->getLanguage('polski')],
            ]);

        return $languageService;
    }

    protected function getLanguageResolverMock(): LanguageResolver
    {
        $languageResolver = $this->createMock(LanguageResolver::class);
        $languageResolver
            ->method('getPrioritizedLanguages')
            ->willReturn(['eng-GB', 'pol-PL']);

        return $languageResolver;
    }
}
