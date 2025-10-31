<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Entity\Transaction;
use CreditBundle\Model\ConsumptionPreview;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(ConsumptionPreview::class)]
final class ConsumptionPreviewTest extends TestCase
{
    public function testBasicPreviewCreation(): void
    {
        $records = [];
        $preview = new ConsumptionPreview($records, false, 0);

        // 验证预览对象属性
        self::assertSame([], $preview->getRecords());
        self::assertFalse($preview->needsMerge());
        self::assertSame(0, $preview->getRecordCount());
    }

    public function testPreviewWithData(): void
    {
        // 使用真实的 Transaction 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Preview Account');
        $transaction = TestDataFactory::createTransaction('TEST-PREVIEW', $account, 100.0);
        $records = [$transaction];
        $preview = new ConsumptionPreview($records, true, 1);

        self::assertSame($records, $preview->getRecords());
        self::assertTrue($preview->needsMerge());
        self::assertSame(1, $preview->getRecordCount());
    }

    public function testNeedsMerge(): void
    {
        $previewFalse = new ConsumptionPreview([], false, 0);
        self::assertFalse($previewFalse->needsMerge());

        $previewTrue = new ConsumptionPreview([], true, 0);
        self::assertTrue($previewTrue->needsMerge());
    }
}
