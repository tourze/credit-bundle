<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AdminMenu;
use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    protected function onSetUp(): void
    {
        // 设置测试环境
    }

    public function testMenuCreation(): void
    {
        // 使用容器获取服务
        $menu = self::getService(AdminMenu::class);

        // 测试对象可以被调用
        self::assertIsCallable($menu);
    }

    public function testMenuInvoke(): void
    {
        $menu = self::getService(AdminMenu::class);

        $childMenu = $this->createMock(ItemInterface::class);

        $item = $this->createMock(ItemInterface::class);
        // 直接模拟 '积分中心' 子菜单已存在的情况
        $item->method('getChild')->with('积分中心')->willReturn($childMenu);
        $item->method('addChild')->with('积分中心')->willReturn($childMenu);

        $menu->__invoke($item);
    }
}
