<?php

namespace CreditBundle\Tests\Stub;

use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @implements PaginationInterface<mixed, mixed>
 */
class PaginationStub implements PaginationInterface, \IteratorAggregate
{
    public function getItems(): iterable
    {
        return [];
    }

    public function getTotalItemCount(): int
    {
        return 0;
    }

    public function count(): int
    {
        return 0;
    }

    public function getCurrentPageNumber(): int
    {
        return 1;
    }

    public function getItemNumberPerPage(): int
    {
        return 10;
    }

    public function setCurrentPageNumber(int $pageNumber): void
    {
    }

    public function setItemNumberPerPage(int $itemsPerPage): void
    {
    }

    public function setTotalItemCount(int $count): void
    {
    }

    public function setItems(iterable $items): void
    {
    }

    public function setPaginatorOptions(array $options): void
    {
    }

    public function setCustomParameters(array $parameters): void
    {
    }

    public function getCustomParameter(string $name): mixed
    {
        return null;
    }

    public function setCustomParameter(string $name, mixed $value): void
    {
    }

    public function getPaginatorOption(string $name): mixed
    {
        return null;
    }

    public function getRoute(): string
    {
        return '';
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return [];
    }

    public function offsetExists(mixed $offset): bool
    {
        return false;
    }

    public function offsetGet(mixed $offset): mixed
    {
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator(is_array($this->getItems()) ? $this->getItems() : iterator_to_array($this->getItems()));
    }
}
