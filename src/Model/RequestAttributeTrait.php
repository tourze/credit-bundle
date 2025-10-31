<?php

declare(strict_types=1);

namespace CreditBundle\Model;

trait RequestAttributeTrait
{
    /**
     * 管理其它表主键信息
     */
    private string $relationId = '';

    /**
     * 关联模型类
     */
    private string $relationModel = '';

    public function getRelationId(): string
    {
        return $this->relationId;
    }

    public function setRelationId(string $relationId): void
    {
        $this->relationId = $relationId;
    }

    public function getRelationModel(): string
    {
        return $this->relationModel;
    }

    public function setRelationModel(string $relationModel): void
    {
        $this->relationModel = $relationModel;
    }
}
