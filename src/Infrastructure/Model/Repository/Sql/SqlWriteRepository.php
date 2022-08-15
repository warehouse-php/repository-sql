<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

use Doctrine\DBAL\Connection;
use Warehouse\Domain\Model\Repository\FilterProcessorInterface;
use Warehouse\Domain\Model\Repository\SortProcessorInterface;
use Warehouse\Domain\Model\Repository\WriteRepositoryInterface;

final class SqlWriteRepository implements WriteRepositoryInterface {
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

  public function add(EntityInterface ...$entities): static {

    return $this;
  }

  public function remove(EntityInterface ...$entities): static {

    return $this;
  }

  public function inTransaction(callable $transaction): static {
    try {
      $this->connection->beginTransaction();
      $transaction();
      $this->connection->commit();

      return $this;
    } catch (Exception $exception) {
      $this->connection->rollBack();

      throw $exception;
    }
  }
}
