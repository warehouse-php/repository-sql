<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Result;
use Exception;
use Iterator;
use Kolekto\CollectionInterface;
use Kolekto\LazyCollection;
use Warehouse\Domain\Model\Exception\RecordNotFoundException;
use Warehouse\Domain\Model\Repository\EntityInterface;
use Warehouse\Domain\Model\Repository\FilterProcessorInterface;
use Warehouse\Domain\Model\Repository\PageInterface;
use Warehouse\Domain\Model\Repository\PaginatedRepositoryInterface;
use Warehouse\Domain\Model\Repository\ReadRepositoryInterface;
use Warehouse\Domain\Model\Repository\RepositoryInterface;
use Warehouse\Domain\Model\Repository\SortProcessorInterface;
use Warehouse\Domain\Model\Repository\WriteRepositoryInterface;

final class SqlRepository implements PaginatedRepositoryInterface, ReadRepositoryInterface, RepositoryInterface, WriteRepositoryInterface {
  private PaginatedRepositoryInterface $paginatedRepository;
  private ReadRepositoryInterface $readRepository;
  private WriteRepositoryInterface $writeRepository;

  public function __construct(
    FilterProcessorInterface $filterProcessor,
    SortProcessorInterface $sortProcessor,
    MappingInterface $mapping,
    string $dsn
  ) {
    $connection = DriverManager::getConnection($dsn);

    $this->paginatedRepository = new SqlPaginatedRepository(
      $filterProcessor,
      $sortProcessor,
      $mapping,
      $connection
    );

    $this->readRepository = new SqlReadRepository(
      $filterProcessor,
      $sortProcessor,
      $mapping,
      $connection
    );

    $this->writeRepository = new SqlWriteRepository(
      $filterProcessor,
      $sortProcessor,
      $mapping,
      $connection
    );
  }

  /** PaginatedRepositoryInterface **/
  public function findAll(CursorInterface $cursor = null): PageInterface {
    $this->paginatedRepository->findAll($cursor);
  }

  /** ReadRepositoryInterface **/
  public function find(mixed $id, FieldsInterface $fields = null): EntityInterface {
    return $this->readRepository->find($id, $fields);
  }

  public function findBy(
    FilterInterface $filter,
    Sort $sort = null,
    FieldsInterface $fields = null
  ): CollectionInterface {
    return $this->readRepository->findBy($filter, $sort, $fields);
  }

  public function findDistinctBy(
    FieldsInterface $distinctFields,
    FilterInterface $filter = null,
    Sort $sort = null,
    FieldsInterface $fields = null
  ): CollectionInterface {
    return $this->readRepository->findDistinctBy($distinctFields, $filter, $sort, $fields);
  }

  /** RepositoryInterface **/
  public function count(FilterInterface $filter = null): int {
    $queryBuilder = $this->connection->createQueryBuilder();

    $query = $queryBuilder
      ->select('COUNT(*) AS "total"')
      ->from($this->mapping->getTableName());

    if ($filter !== null) {
      $query = $this->filterProcessor
        ->setQueryBuilder($query)
        ->apply($filter)
        ->getQueryBuilder();
    }

    $result = $query
      ->executeQuery()
      ->fetchAssociative();

    return (int)$result['total'];
  }

  public function exists(mixed $id): bool {
    $queryBuilder = $this->connection->createQueryBuilder();
    $result = $queryBuilder
      ->select('*')
      ->from($this->mapping->getTableName())
      ->andWhere(
        $queryBuilder->expr()->eq($this->mapping->getIdColumnName(), ':id')
      )
      ->setMaxResults(1)
      ->setParameter('id', $id)
      ->executeQuery()
      ->fetchAssociative();

    return count($result) > 0;
  }

  /** WriteRepositoryInterface **/
  public function add(EntityInterface ...$entities): static {
    $this->writeRepository->add($entities);

    return $this;
  }

  public function remove(EntityInterface ...$entities): static {
    $this->writeRepository->remove($entities);

    return $this;
  }

  public function inTransaction(callable $transaction): static {
    $this->writeRepository->inTransaction($transaction);

    return $this;
  }
}
