<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Model\ConsumptionPreview;
use PHPUnit\Framework\TestCase;

class ConsumptionPreviewTest extends TestCase
{
    public function testBasicPreviewCreation(): void
    {
        $records = [];
        $preview = new ConsumptionPreview($records, false, 0);

        self::assertInstanceOf(ConsumptionPreview::class, $preview);
        self::assertSame([], $preview->getRecords());
        self::assertFalse($preview->needsMerge());
        self::assertSame(0, $preview->getRecordCount());
    }

    public function testPreviewWithData(): void
    {
        $transaction = $this->createMock(\CreditBundle\Entity\Transaction::class);
        $records = [$transaction];
        $preview = new ConsumptionPreview($records, true, 1);

        self::assertSame($records, $preview->getRecords());
        self::assertTrue($preview->needsMerge());
        self::assertSame(1, $preview->getRecordCount());
    }
}
