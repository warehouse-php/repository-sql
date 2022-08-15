<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

use Doctrine\DBAL\Connection;
use Warehouse\Domain\Model\Repository\FilterProcessorInterface;
use Warehouse\Domain\Model\Repository\PageInterface;
use Warehouse\Domain\Model\Repository\PaginatedRepositoryInterface;
use Warehouse\Domain\Model\Repository\SortProcessorInterface;

final class SqlPaginatedRepository implements PaginatedRepositoryInterface {
  private FilterProcessorInterface $filterProcessor;
  private SortProcessorInterface $sortProcessor;
  private MappingInterface $mapping;
  private Connection $connection;

  public function __construct(
    FilterProcessorInterface $filterProcessor,
    SortProcessorInterface $sortProcessor,
    MappingInterface $mapping,
    Connection $connection
  ) {
    $this->filterProcessor = $filterProcessor;
    $this->sortProcessor   = $sortProcessor;
    $this->mapping         = $mapping;
    $this->connection      = $connection;
  }

  public function findAll(CursorInterface $cursor = null): PageInterface {
  }
}
