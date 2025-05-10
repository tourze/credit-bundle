<?php

namespace CreditBundle\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractTestCase extends TestCase
{
    /**
     * 创建模拟用户
     */
    protected function createMockUser(): UserInterface
    {
        $user = $this->createMock(UserInterface::class);
        $user->method('getUserIdentifier')->willReturn('test-user');
        
        return $user;
    }
    
    /**
     * 生成随机小数金额
     */
    protected function generateRandomAmount(float $min = 0.01, float $max = 1000): float
    {
        return round(mt_rand($min * 100, $max * 100) / 100, 2);
    }
    
    /**
     * 生成随机事件号
     */
    protected function generateEventNo(): string
    {
        return 'S' . time() . mt_rand(1000, 9999);
    }
} 