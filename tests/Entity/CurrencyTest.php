<?php

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Currency;
use CreditBundle\Tests\AbstractTestCase;

class CurrencyTest extends AbstractTestCase
{
    /**
     * 测试新建货币实体默认值
     */
    public function testConstruct_createsNewCurrencyWithDefaultValues(): void
    {
        $currency = new Currency();

        $this->assertEquals(0, $currency->getId());
    }

    /**
     * 测试币种代码存取
     */
    public function testGetSetCurrency_properlyStoresAndRetrievesCurrency(): void
    {
        $currency = new Currency();
        $code = 'USD';

        $currency->setCurrency($code);

        $this->assertEquals($code, $currency->getCurrency());
    }

    /**
     * 测试名称存取
     */
    public function testGetSetName_properlyStoresAndRetrievesName(): void
    {
        $currency = new Currency();
        $name = '美元';

        $currency->setName($name);

        $this->assertEquals($name, $currency->getName());
    }

    /**
     * 测试是否主币种存取
     */
    public function testGetSetMain_properlyStoresAndRetrievesMain(): void
    {
        $currency = new Currency();

        // 先初始化属性，再测试
        $currency->setMain(false);
        $this->assertFalse($currency->getMain());

        $currency->setMain(true);
        $this->assertTrue($currency->getMain());
    }

    /**
     * 测试备注存取
     */
    public function testGetSetRemark_properlyStoresAndRetrievesRemark(): void
    {
        $currency = new Currency();
        $remark = 'Test remark';

        $currency->setRemark($remark);

        $this->assertEquals($remark, $currency->getRemark());
    }
}
