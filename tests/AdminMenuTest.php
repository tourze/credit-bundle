<?php

declare(strict_types=1);

namespace CreditBundle\Tests;

use CreditBundle\AdminMenu;
use PHPUnit\Framework\TestCase;

class AdminMenuTest extends TestCase
{
    public function testMenuCreation(): void
    {
        $linkGenerator = $this->createMock(\Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface::class);
        $menu = new AdminMenu($linkGenerator);

        self::assertInstanceOf(AdminMenu::class, $menu);
    }

    public function testMenuInvoke(): void
    {
        $linkGenerator = $this->createMock(\Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface::class);
        $linkGenerator->method('getCurdListPage')->willReturn('/test-url');
        
        $menu = new AdminMenu($linkGenerator);

        $childMenu = $this->createMock(\Knp\Menu\ItemInterface::class);
        $childMenu->method('addChild')->willReturnSelf();
        $childMenu->method('setUri')->willReturnSelf();

        $item = $this->createMock(\Knp\Menu\ItemInterface::class);
        // 直接模拟 '积分中心' 子菜单已存在的情况
        $item->method('getChild')->with('积分中心')->willReturn($childMenu);
        $item->method('addChild')->with('积分中心')->willReturn($childMenu);

        $menu->__invoke($item);

        // 验证菜单调用完成
        $this->addToAssertionCount(1);
    }
}
