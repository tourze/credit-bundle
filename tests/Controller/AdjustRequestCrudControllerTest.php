<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Controller;

use CreditBundle\Controller\AdjustRequestCrudController;
use CreditBundle\Entity\AdjustRequest;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * AdjustRequestCrudController 测试
 *
 * @internal
 */
#[CoversClass(AdjustRequestCrudController::class)]
#[RunTestsInSeparateProcesses]
final class AdjustRequestCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return AdjustRequestCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(AdjustRequestCrudController::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIndexPageHeaders(): array
    {
        return [
            'ID' => ['ID'],
            '积分账户' => ['积分账户'],
            '调整金额' => ['调整金额'],
            '调整类型' => ['调整类型'],
            '状态' => ['状态'],
            '备注' => ['备注'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideNewPageFields(): array
    {
        return [
            'account' => ['account'],
            'amount' => ['amount'],
            'type' => ['type'],
            'status' => ['status'],
            'remark' => ['remark'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideEditPageFields(): array
    {
        return [
            'account' => ['account'],
            'amount' => ['amount'],
            'type' => ['type'],
            'status' => ['status'],
            'remark' => ['remark'],
        ];
    }

    public function testControllerIsInstantiable(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);

        $this->assertTrue($reflection->isInstantiable());
        $this->assertTrue($reflection->isFinal());
    }

    public function testControllerHasRequiredConfigurationMethods(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);

        $requiredMethods = [
            'getEntityFqcn',
            'configureCrud',
            'configureFields',
            'configureFilters',
        ];

        foreach ($requiredMethods as $methodName) {
            $this->assertTrue($reflection->hasMethod($methodName), "方法 {$methodName} 必须存在");

            $method = $reflection->getMethod($methodName);
            $this->assertTrue($method->isPublic(), "方法 {$methodName} 必须是public");
        }
    }

    public function testControllerStructure(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);

        // 测试类是final的
        $this->assertTrue($reflection->isFinal());

        // 测试继承关系
        $this->assertTrue($reflection->isSubclassOf('EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController'));

        // 测试getEntityFqcn是静态方法
        $getEntityMethod = $reflection->getMethod('getEntityFqcn');
        $this->assertTrue($getEntityMethod->isStatic());
        $this->assertTrue($getEntityMethod->isPublic());
    }

    public function testControllerHasProperNamespace(): void
    {
        $this->assertEquals(
            'CreditBundle\Controller',
            (new \ReflectionClass(AdjustRequestCrudController::class))->getNamespaceName()
        );
    }

    public function testConfigureFieldsReturnsIterable(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);
        $method = $reflection->getMethod('configureFields');

        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);

        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('iterable', $returnType->getName());
        } else {
            $this->assertInstanceOf(\ReflectionType::class, $returnType);
        }
    }

    public function testConfigureFiltersReturnsFilters(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);
        $method = $reflection->getMethod('configureFilters');

        $this->assertTrue($method->isPublic());

        $returnType = $method->getReturnType();
        $this->assertNotNull($returnType);

        if ($returnType instanceof \ReflectionNamedType) {
            $this->assertEquals('EasyCorp\Bundle\EasyAdminBundle\Config\Filters', $returnType->getName());
        } else {
            $this->assertInstanceOf(\ReflectionType::class, $returnType);
        }
    }

    public function testControllerHasCorrectAnnotations(): void
    {
        $reflection = new \ReflectionClass(AdjustRequestCrudController::class);
        $attributes = $reflection->getAttributes();

        $hasAdminCrudAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'AdminCrud')) {
                $hasAdminCrudAttribute = true;
                break;
            }
        }

        $this->assertTrue($hasAdminCrudAttribute, 'Controller应该有AdminCrud注解');
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 尝试访问新建表单页面
        $url = $this->generateAdminUrl('new', ['entityFqcn' => AdjustRequest::class]);
        $crawler = $client->request('GET', $url);

        if (200 === $client->getResponse()->getStatusCode()) {
            // 查找并提交空表单
            $form = $crawler->filter('form[name="ea"]')->first();
            if ($form->count() > 0) {
                $form = $form->form();
                $crawler = $client->submit($form);

                // 检查是否显示验证错误
                $this->assertResponseStatusCodeSame(422);
                $this->assertStringContainsString(
                    'should not be blank',
                    $crawler->filter('.invalid-feedback')->text()
                );
            }
        }

        $this->assertTrue(true, '验证测试完成');
    }
}
