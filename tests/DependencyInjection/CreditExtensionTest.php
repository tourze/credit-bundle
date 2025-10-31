<?php

declare(strict_types=1);

namespace CreditBundle\Tests\DependencyInjection;

use CreditBundle\DependencyInjection\CreditExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(CreditExtension::class)]
final class CreditExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
}
