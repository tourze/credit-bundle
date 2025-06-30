<?php

namespace CreditBundle\Tests\Unit\EventListener;

use CreditBundle\Entity\Currency;
use CreditBundle\EventListener\CurrencyListener;
use CreditBundle\Repository\AccountRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tourze\JsonRPC\Core\Exception\ApiException;

class CurrencyListenerTest extends TestCase
{
    private MockObject&AccountRepository $accountRepository;
    private CurrencyListener $listener;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepository::class);
        $this->listener = new CurrencyListener($this->accountRepository);
    }

    public function testPreRemove_WithNoAccounts_DoesNotThrowException(): void
    {
        $currency = $this->createMock(Currency::class);
        $currency->method('getCurrency')->willReturn('USD');

        $this->accountRepository
            ->expects($this->once())
            ->method('count')
            ->with(['currency' => 'USD'])
            ->willReturn(0);

        $this->listener->preRemove($currency);
        
        $this->assertTrue(true);
    }

    public function testPreRemove_WithExistingAccounts_ThrowsApiException(): void
    {
        $currency = $this->createMock(Currency::class);
        $currency->method('getCurrency')->willReturn('USD');

        $this->accountRepository
            ->expects($this->once())
            ->method('count')
            ->with(['currency' => 'USD'])
            ->willReturn(5);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('该积分已有人使用，不允许删除[1]');

        $this->listener->preRemove($currency);
    }
}