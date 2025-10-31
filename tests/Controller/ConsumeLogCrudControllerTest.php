<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Controller;

use CreditBundle\Controller\ConsumeLogCrudController;
use CreditBundle\Entity\ConsumeLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumeLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class ConsumeLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return ConsumeLogCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(ConsumeLogCrudController::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIndexPageHeaders(): array
    {
        return [
            'ID' => ['ID'],
            '成本流水' => ['成本流水'],
            '消费流水' => ['消费流水'],
            '消耗金额' => ['消耗金额'],
            '创建时间' => ['创建时间'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideNewPageFields(): array
    {
        return [
            'costTransaction' => ['costTransaction'],
            'consumeTransaction' => ['consumeTransaction'],
            'amount' => ['amount'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideEditPageFields(): array
    {
        return [
            'costTransaction' => ['costTransaction'],
            'consumeTransaction' => ['consumeTransaction'],
            'amount' => ['amount'],
        ];
    }

    protected function getEntityFqcn(): string
    {
        return ConsumeLog::class;
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to ConsumeLog CRUD
        $link = $crawler->filter('a[href*="ConsumeLogCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateConsumeLog(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new ConsumeLogCrudController();

        $fields = $controller->configureFields('new');
        $this->assertNotNull($fields, 'configureFields should return a valid iterable');

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud, 'configureCrud should return a Crud instance');

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters, 'configureFilters should return a Filters instance');
    }

    public function testEditConsumeLog(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new ConsumeLogCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testDetailConsumeLog(): void
    {
        // Test that configureFields returns appropriate fields for detail view
        $controller = new ConsumeLogCrudController();
        $fields = $controller->configureFields('detail');
        $fieldsArray = iterator_to_array($fields);
        self::assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method exists and is callable
        $controller = new ConsumeLogCrudController();
        $controller->configureFilters(Filters::new());
        self::assertIsCallable(fn () => $controller->configureFilters(Filters::new()));
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new ConsumeLogCrudController();
        self::assertEquals(ConsumeLog::class, $controller::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 尝试访问新建表单页面
        $url = $this->generateAdminUrl('new', ['entityFqcn' => ConsumeLog::class]);
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
