<?php

declare(strict_types=1);

namespace CreditBundle\Tests;

use CreditBundle\CreditBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(CreditBundle::class)]
#[RunTestsInSeparateProcesses]
final class CreditBundleTest extends AbstractBundleTestCase
{
}
