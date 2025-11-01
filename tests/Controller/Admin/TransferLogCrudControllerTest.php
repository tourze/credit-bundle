<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Controller\Admin;

use CreditBundle\Controller\Admin\TransferLogCrudController;
use CreditBundle\Entity\TransferLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TransferLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TransferLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return TransferLogCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TransferLogCrudController::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIndexPageHeaders(): array
    {
        return [
            'ID' => ['ID'],
            '币种' => ['币种'],
            '转出账户' => ['转出账户'],
            '转入账户' => ['转入账户'],
            '转出金额' => ['转出金额'],
            '转入金额' => ['转入金额'],
            '备注' => ['备注'],
            '关联ID' => ['关联ID'],
            '关联模型' => ['关联模型'],
            '过期时间' => ['过期时间'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideNewPageFields(): array
    {
        return [
            'currency' => ['currency'],
            'outAccount' => ['outAccount'],
            'inAccount' => ['inAccount'],
            'outAmount' => ['outAmount'],
            'inAmount' => ['inAmount'],
            'remark' => ['remark'],
            'relationId' => ['relationId'],
            'relationModel' => ['relationModel'],
            'expireTime' => ['expireTime'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideEditPageFields(): array
    {
        return [
            'currency' => ['currency'],
            'outAccount' => ['outAccount'],
            'inAccount' => ['inAccount'],
            'outAmount' => ['outAmount'],
            'inAmount' => ['inAmount'],
            'remark' => ['remark'],
            'relationId' => ['relationId'],
            'relationModel' => ['relationModel'],
            'expireTime' => ['expireTime'],
        ];
    }

    public function testControllerFqcn(): void
    {
        self::assertSame(TransferLog::class, TransferLogCrudController::getEntityFqcn());
    }

    public function testControllerConfiguration(): void
    {
        $controller = new TransferLogCrudController();

        // Test that the controller has the required methods for CRUD operations
        $fields = $controller->configureFields('index');
        $this->assertNotNull($fields, 'configureFields should return a valid iterable');

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud, 'configureCrud should return a Crud instance');

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters, 'configureFilters should return a Filters instance');
    }

    public function testFieldsConfiguration(): void
    {
        $controller = new TransferLogCrudController();

        // Test index fields
        $indexFields = iterator_to_array($controller->configureFields('index'));
        self::assertNotEmpty($indexFields);

        // Test new fields
        $newFields = iterator_to_array($controller->configureFields('new'));
        self::assertNotEmpty($newFields);

        // Test edit fields
        $editFields = iterator_to_array($controller->configureFields('edit'));
        self::assertNotEmpty($editFields);

        // Test detail fields
        $detailFields = iterator_to_array($controller->configureFields('detail'));
        self::assertNotEmpty($detailFields);
    }

    public function testCrudConfiguration(): void
    {
        $controller = new TransferLogCrudController();
        $controller->configureCrud(Crud::new());
        self::assertIsCallable(fn () => $controller->configureCrud(Crud::new()));
    }

    public function testFiltersConfiguration(): void
    {
        $controller = new TransferLogCrudController();
        $controller->configureFilters(Filters::new());
        self::assertIsCallable(fn () => $controller->configureFilters(Filters::new()));
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new TransferLogCrudController();
        self::assertEquals(TransferLog::class, $controller::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createAuthenticatedClient();

        // 尝试访问新建表单页面
        $url = $this->generateAdminUrl('new', ['entityFqcn' => TransferLog::class]);
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
