<?php

namespace CreditBundle\Command;

use CreditBundle\Repository\CurrencyRepository;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * 读取xls文件并一行行解析后进行积分调整，xls表头有“积分名”“账户名”“变更数值”“变更备注”
 */
#[AsCommand(name: 'credit:batch-adjust', description: '通过xls文件调整积分')]
class BatchAdjustCommand extends Command
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly AccountService $accountService,
        private readonly CurrencyRepository $currencyRepository,
        private readonly Snowflake $snowflake,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('文件路径')
            ->addArgument('xlsFileName', InputArgument::REQUIRED, '文件路径');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectDir = $this->getApplication()->getKernel()->getProjectDir();

        $filePath = $projectDir . $input->getArgument('xlsFileName');

        if (!file_exists($filePath)) {
            $output->writeln('任务credit:batch-adjust文件不存在');

            return Command::FAILURE;
        }

        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getSheet(0);
        $rowIndex = 0;
        foreach ($sheet->getRowIterator() as $row) {
            // 如果是第一行（索引为0），则跳过
            if (0 === $rowIndex) {
                ++$rowIndex;
                continue;
            }

            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $value = $cell->getValue();
                $rowData[] = $value;
            }
            $currency = $this->currencyRepository->findOneBy(['name' => $rowData[3]]);
            if (!$currency) {
                $output->writeln('任务credit:batch-adjust currency数据不存在');

                return Command::FAILURE;
            }
            $account = $this->accountService->getAccountByName($rowData[0], $currency);
            // 正数是加积分，负数减积分
            if ($rowData[1] > 0) {
                $this->transactionService->increase($this->snowflake->id(), $account, $rowData[1], $rowData[2]);
            } elseif ($rowData[1] < 0) {
                $this->transactionService->decrease($this->snowflake->id(), $account, $rowData[1], $rowData[2]);
            } else {
                $output->writeln('任务credit:batch-adjust变更数值错误');

                return Command::FAILURE;
            }
            ++$rowIndex;
        }

        return Command::SUCCESS;
    }
}
