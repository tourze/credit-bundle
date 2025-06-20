<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Currency;
use CreditBundle\Repository\CurrencyRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class CurrencyService
{
    public function __construct(
        private readonly CurrencyRepository $currencyRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 获取指定名称的币种记录
     */
    public function getCurrencyByCode(string $currency): ?Currency
    {
        return $this->currencyRepository->findOneBy(['currency' => $currency]);
    }

    /**
     * 获取主积分币种
     */
    public function getMainCurrency(): ?Currency
    {
        // TODO 等其他逻辑调整了，迁移过来
        return null;
    }

    /**
     * 获取指定名称的币种记录
     */
    public function ensureCurrencyByCode(string $currency, ?string $name = null): Currency
    {
        $dbItem = null;
        $retryTimes = 10;

        while (null === $dbItem && $retryTimes > 0) {
            $dbItem = $this->currencyRepository->findOneBy(['currency' => $currency]);
            if ($dbItem === null) {
                $dbItem = new Currency();
                $dbItem->setCurrency($currency);
                $dbItem->setName($name ?: $currency);
                $dbItem->setValid(true);
                $dbItem->setRemark(time() . '自动生成');

                try {
                    $this->entityManager->persist($dbItem);
                    $this->entityManager->flush();
                } catch (UniqueConstraintViolationException $exception) {
                    $this->logger->warning('自动创建币种失败', [
                        'currency' => $currency,
                        'name' => $name,
                        'retryTimes' => $retryTimes,
                        'exception' => $exception,
                    ]);
                    $dbItem = null;
                    --$retryTimes;
                }
            }
        }

        return $dbItem;
    }
}
