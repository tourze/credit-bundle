<?php

namespace CreditBundle\EventSubscriber;

use AntdCpBundle\Event\CreateRecordEvent;
use AntdCpBundle\Event\ModifyRecordEvent;
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use CreditBundle\Service\TransactionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tourze\JsonRPC\Core\Exception\ApiException;
use Tourze\SnowflakeBundle\Service\Snowflake;

class OperateAdjustRequest implements EventSubscriberInterface
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly Snowflake $snowflake,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ModifyRecordEvent::class => 'onEditActivity',
            CreateRecordEvent::class => 'onEditActivity',
        ];
    }

    /**
     * 编辑时的检查
     *
     * @throws ApiException
     */
    public function onEditActivity(ModifyRecordEvent|CreateRecordEvent $event): void
    {
        $entity = $event->getModel();
        if ($entity instanceof AdjustRequest) {
            $event->getModel()->getId();
            $form = $event->getForm();
            // 状态为通过
            if ($form['status'] == AdjustRequestStatus::PASS->value) {
                // 判断增加/减少
                if ($form['type'] == AdjustRequestType::INCREASE->value) {
                    $this->transactionService->increase($this->snowflake->id(), $event->getModel()->getAccount(), $form['amount'], $form['remark']);
                } elseif ($form['type'] == AdjustRequestType::DECREASE->value) {
                    $this->transactionService->decrease($this->snowflake->id(), $event->getModel()->getAccount(), $form['amount'], $form['remark']);
                } else {
                    throw new ApiException('类型错误');
                }
            }
        }
    }
}
