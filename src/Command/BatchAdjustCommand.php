<?php

declare(strict_types=1);

namespace CreditBundle\Command;

use CreditBundle\Entity\Account;
use CreditBundle\Exception\InvalidAmountException;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 读取xls文件并一行行解析后进行积分调整，xls表头有“积分名”“账户名”“变更数值”“变更备注”
 */
#[AsCommand(name: self::NAME, description: '通过xls文件调整积分')]
class BatchAdjustCommand extends Command
{
    public const NAME = 'credit:batch-adjust';

    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly AccountService $accountService,
        private readonly Snowflake $snowflake,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('文件路径')
            ->addArgument('xlsFileName', InputArgument::REQUIRED, '文件路径')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $this->getFilePath($input);

        if (!file_exists($filePath)) {
            $output->writeln('任务credit:batch-adjust文件不存在');

            return Command::FAILURE;
        }

        try {
            $this->processSpreadsheet($filePath, $output);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('任务credit:batch-adjust处理失败: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function getFilePath(InputInterface $input): string
    {
        $projectDir = $this->kernel->getProjectDir();
        $fileName = $input->getArgument('xlsFileName');

        if (!is_string($fileName)) {
            throw new \InvalidArgumentException('File name must be a string');
        }

        return $projectDir . '/' . $fileName;
    }

    private function processSpreadsheet(string $filePath, OutputInterface $output): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rowIndex = 0;

        foreach ($sheet->getRowIterator() as $row) {
            if ($this->shouldSkipRow($rowIndex)) {
                ++$rowIndex;
                continue;
            }

            $rowData = $this->extractRowData($row);
            $this->processRowData($rowData, $output);
            ++$rowIndex;
        }
    }

    private function shouldSkipRow(int $rowIndex): bool
    {
        return 0 === $rowIndex; // 跳过第一行
    }

    /**
     * @return array<int, mixed>
     */
    private function extractRowData(mixed $row): array
    {
        if (!is_object($row) || !method_exists($row, 'getCellIterator')) {
            throw new \InvalidArgumentException('Invalid row object provided');
        }

        $rowData = [];
        $cellIterator = $row->getCellIterator();

        if (!is_object($cellIterator) || !method_exists($cellIterator, 'setIterateOnlyExistingCells')) {
            throw new \InvalidArgumentException('Invalid cell iterator object');
        }

        $cellIterator->setIterateOnlyExistingCells(false);

        if (!is_iterable($cellIterator)) {
            throw new \InvalidArgumentException('Cell iterator must be iterable');
        }

        foreach ($cellIterator as $cell) {
            if (!is_object($cell) || !method_exists($cell, 'getValue')) {
                throw new \InvalidArgumentException('Invalid cell object');
            }

            $value = $cell->getValue();
            $rowData[] = $value;
        }

        return $rowData;
    }

    /**
     * @param array<int, mixed> $rowData
     */
    private function processRowData(array $rowData, OutputInterface $output): void
    {
        if (!isset($rowData[0], $rowData[1], $rowData[2], $rowData[3])) {
            throw new \InvalidArgumentException('Row data must contain at least 4 elements');
        }

        if (!is_scalar($rowData[0]) || !is_scalar($rowData[1]) || !is_scalar($rowData[2]) || !is_scalar($rowData[3])) {
            throw new \InvalidArgumentException('Row data elements must be scalar values');
        }

        $name = (string) $rowData[0];
        $amount = (float) $rowData[1];
        $reason = (string) $rowData[2];
        $currency = (string) $rowData[3];

        $account = $this->accountService->getAccountByName($name, $currency);
        $this->adjustAccount($account, $amount, $reason, $output);
    }

    private function adjustAccount(Account $account, float $amount, string $reason, OutputInterface $output): void
    {
        if ($amount > 0) {
            $this->transactionService->increase($this->snowflake->id(), $account, $amount, $reason);
        } elseif ($amount < 0) {
            $this->transactionService->decrease($this->snowflake->id(), $account, $amount, $reason);
        } else {
            throw new InvalidAmountException('变更数值错误');
        }
    }
}
