<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace CodeRhapsodie\Tests\Integration\ConnectorGemini;

use Ibexa\Contracts\Core\Test\IbexaKernelTestCase;

/**
 * @coversNothing
 */
final class BasicKernelTest extends IbexaKernelTestCase
{
    public function testCompilesSuccessfully(): void
    {
        self::bootKernel();

        $this->expectNotToPerformAssertions();
    }
}
