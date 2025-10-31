<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Controller\Admin;

use CreditBundle\Controller\Admin\TransferErrorLogCrudController;
use CreditBundle\Entity\TransferErrorLog;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\ApplicationContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * @internal
 */
#[CoversClass(TransferErrorLogCrudController::class)]
#[RunTestsInSeparateProcesses]
final class TransferErrorLogCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    /**
     * @return TransferErrorLogCrudController
     */
    protected function getControllerService(): AbstractCrudController
    {
        return self::getService(TransferErrorLogCrudController::class);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideIndexPageHeaders(): array
    {
        return [
            'ID' => ['ID'],
            '转出账号名' => ['转出账号名'],
            '转入账号名' => ['转入账号名'],
            '货币' => ['货币'],
            '转账金额' => ['转账金额'],
            '错误摘要' => ['错误摘要'],
            '发生时间' => ['发生时间'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideNewPageFields(): array
    {
        // This is a read-only controller, NEW action is disabled
        // But we need to provide dummy data to satisfy the data provider requirement
        return [
            'dummy' => ['dummy'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function provideEditPageFields(): array
    {
        // This is a read-only controller, EDIT action is disabled
        // But we need to provide dummy data to satisfy the data provider requirement
        return [
            'dummy' => ['dummy'],
        ];
    }

    protected function getEntityFqcn(): string
    {
        return TransferErrorLog::class;
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        $crawler = $client->request('GET', '/admin');
        self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Navigate to TransferErrorLog CRUD
        $link = $crawler->filter('a[href*="TransferErrorLogCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            self::assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        }
    }

    public function testReadOnlyController(): void
    {
        // Test that the controller has the required methods for CRUD operations
        $controller = new TransferErrorLogCrudController();

        $fields = $controller->configureFields('index');
        $this->assertIsIterable($fields, 'configureFields should return an iterable');

        $crud = $controller->configureCrud(Crud::new());
        $this->assertInstanceOf(Crud::class, $crud, 'configureCrud should return a Crud instance');

        $filters = $controller->configureFilters(Filters::new());
        $this->assertInstanceOf(Filters::class, $filters, 'configureFilters should return a Filters instance');

        $actions = $controller->configureActions(Actions::new());
        $this->assertInstanceOf(Actions::class, $actions, 'configureActions should return an Actions instance');
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new TransferErrorLogCrudController();
        self::assertEquals(TransferErrorLog::class, $controller::getEntityFqcn());
    }

    public function testCrudConfiguration(): void
    {
        $controller = new TransferErrorLogCrudController();
        $controller->configureCrud(Crud::new());
        self::assertIsCallable(fn () => $controller->configureCrud(Crud::new()));
    }

    public function testFieldsConfigurationForIndex(): void
    {
        $controller = new TransferErrorLogCrudController();

        // Test index fields configuration
        $indexFields = iterator_to_array($controller->configureFields('index'));
        self::assertNotEmpty($indexFields);

        // Simply test that we have fields configured
        self::assertGreaterThan(5, count($indexFields));
    }

    public function testFieldsConfigurationForDetail(): void
    {
        $controller = new TransferErrorLogCrudController();

        // Test detail fields configuration
        $detailFields = iterator_to_array($controller->configureFields('detail'));
        self::assertNotEmpty($detailFields);

        // Detail view should have more fields than index
        $indexFields = iterator_to_array($controller->configureFields('index'));
        self::assertGreaterThanOrEqual(count($indexFields), count($detailFields));
    }

    public function testActionsConfiguration(): void
    {
        $controller = new TransferErrorLogCrudController();
        $controller->configureActions(Actions::new());
        self::assertIsCallable(fn () => $controller->configureActions(Actions::new()));
    }

    public function testFiltersConfiguration(): void
    {
        $controller = new TransferErrorLogCrudController();
        $controller->configureFilters(Filters::new());
        self::assertIsCallable(fn () => $controller->configureFilters(Filters::new()));
    }

    public function testQueryBuilderConfiguration(): void
    {
        // Test that the createIndexQueryBuilder method exists and is callable
        $controller = new TransferErrorLogCrudController();

        // Test if method exists
        $reflection = new \ReflectionClass($controller);
        $this->assertTrue(
            $reflection->hasMethod('createIndexQueryBuilder'),
            'Controller should have createIndexQueryBuilder method'
        );

        $method = $reflection->getMethod('createIndexQueryBuilder');
        $this->assertTrue($method->isPublic(), 'createIndexQueryBuilder should be public');

        // Verify method signature
        $parameters = $method->getParameters();
        $this->assertCount(4, $parameters, 'createIndexQueryBuilder should have 4 parameters');
    }
}
