<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\Integration\ConnectorGemini;

use Ibexa\Contracts\Test\Core\Translation\AbstractTranslationCase;

final class TranslationTest extends AbstractTranslationCase
{
    public static function provideConfigNamesForTranslation(): iterable
    {
        yield ['ibexa_connector_gemini'];
    }
}
