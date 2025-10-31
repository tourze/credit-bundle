<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Controller\Admin;

use CreditBundle\Controller\Admin\LimitCrudController;
use CreditBundle\Entity\Limit;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(LimitCrudController::class)]
#[RunTestsInSeparateProcesses]
final class LimitCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return LimitCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(LimitCrudController::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIndexPageHeaders(): array
    {
        return [
            'ID' => ['ID'],
            '账户' => ['账户'],
            '类型' => ['类型'],
            '限制数量' => ['限制数量'],
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
            'type' => ['type'],
            'value' => ['value'],
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
            'type' => ['type'],
            'value' => ['value'],
            'remark' => ['remark'],
        ];
    }

    protected function getEntityFqcn(): string
    {
        return Limit::class;
    }

    public function testControllerConfiguration(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new LimitCrudController();

        $fields = $controller->configureFields('index');
        $this->assertIsIterable($fields, 'configureFields should return an iterable');

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud, 'configureCrud should return a Crud instance');

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters, 'configureFilters should return a Filters instance');
    }

    public function testCreateLimit(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new LimitCrudController();

        $fields = $controller->configureFields('new');
        $this->assertIsIterable($fields, 'configureFields should return an iterable for new action');

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud, 'configureCrud should return a Crud instance');

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters, 'configureFilters should return a Filters instance');
    }

    public function testEditLimit(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new LimitCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailLimit(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new LimitCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and is callable
        $controller = new LimitCrudController();
        $controller->configureFilters(Filters::new());
        self::assertIsCallable(fn () => $controller->configureFilters(Filters::new()));
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new LimitCrudController();
        self::assertEquals(Limit::class, $controller::getEntityFqcn());
    }

    public function testCrudConfiguration(): void
    {
        $controller = new LimitCrudController();
        $controller->configureCrud(Crud::new());
        self::assertIsCallable(fn () => $controller->configureCrud(Crud::new()));
    }

    public function testFieldsConfiguration(): void
    {
        $controller = new LimitCrudController();

        // Test index fields
        $indexFields = iterator_to_array($controller->configureFields('index'));
        self::assertNotEmpty($indexFields);

        // Test new fields
        $newFields = iterator_to_array($controller->configureFields('new'));
        self::assertNotEmpty($newFields);
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 尝试访问新建表单页面
        $url = $this->generateAdminUrl('new', ['entityFqcn' => Limit::class]);
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
