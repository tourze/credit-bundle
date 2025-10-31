<?php

namespace CreditBundle\Tests\Stub;

use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class PaginatorStub implements PaginatorInterface
{
    public function paginate($target, int $page = 1, ?int $limit = null, array $options = []): PaginationInterface
    {
        return new PaginationStub();
    }
}
