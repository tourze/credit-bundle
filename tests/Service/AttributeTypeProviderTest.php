<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AttributeTypeProvider;
use PHPUnit\Framework\TestCase;

class AttributeTypeProviderTest extends TestCase
{
    public function testProviderCreation(): void
    {
        $repository = $this->createMock(\CreditBundle\Repository\CurrencyRepository::class);
        $provider = new AttributeTypeProvider($repository);

        self::assertInstanceOf(AttributeTypeProvider::class, $provider);
    }

    public function testGenSelectData(): void
    {
        $currency = $this->createMock(\CreditBundle\Entity\Currency::class);
        $currency->method('getName')->willReturn('测试币种');
        $currency->method('getCurrency')->willReturn('TEST');

        $repository = $this->createMock(\CreditBundle\Repository\CurrencyRepository::class);
        $repository->method('findBy')->willReturn([$currency]);

        $provider = new AttributeTypeProvider($repository);
        $data = $provider->genSelectData();

        self::assertInstanceOf(\Generator::class, $data);
        
        $dataArray = iterator_to_array($data);
        self::assertNotEmpty($dataArray);
        self::assertArrayHasKey('label', $dataArray[0]);
        self::assertEquals('测试币种', $dataArray[0]['label']);
        self::assertEquals('credit:TEST', $dataArray[0]['value']);
    }
}
