<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Kolekto\CollectionInterface;
use Kolekto\LazyCollection;
use Warehouse\Domain\Model\Repository\FilterProcessorInterface;
use Warehouse\Domain\Model\Repository\ReadRepositoryInterface;
use Warehouse\Domain\Model\Repository\SortProcessorInterface;

final class SqlReadRepository implements ReadRepositoryInterface {
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

  public function find(mixed $id, FieldsInterface $fields = null): EntityInterface {
    $queryBuilder = $this->connection->createQueryBuilder();
    $result = $queryBuilder
      ->select($fields === null ? '*' : $fields->get())
      ->from($this->mapping->getTableName())
      ->andWhere(
        $queryBuilder->expr()->eq($this->mapping->getIdColumnName(), ':id')
      )
      ->setMaxResults(1)
      ->setParameter('id', $id)
      ->executeQuery()
      ->fetchAssociative();

    return $this->hydrate($result, $this->mapping->getEntityClassName());
  }

  public function findBy(
    FilterInterface $filter,
    Sort $sort = null,
    FieldsInterface $fields = null
  ): CollectionInterface {
    $queryBuilder = $this->connection->createQueryBuilder();
    $query = $queryBuilder
      ->select($fields === null ? '*' : $fields->get())
      ->from($this->mapping->getTableName());

    $query = $this->filterProcessor
      ->setQueryBuilder($query)
      ->apply($filter)
      ->getQueryBuilder();

    if ($sort !== null) {
      $query = $this->sortProcessor
        ->setQueryBuilder($query)
        ->apply($sort)
        ->getQueryBuilder();
    }

    $result = $query->executeQuery();

    return new LazyCollection(
      (function (Result $result): Iterator {
        while ($row = $result->fetchAssociative()) {
          yield $this->hydrate($row, $this->mapping->getEntityClassName());
        }
      })->call($this, $result)
    );
  }

  public function findDistinctBy(
    FieldsInterface $distinctFields,
    FilterInterface $filter = null,
    Sort $sort = null,
    FieldsInterface $fields = null
  ): CollectionInterface {
  }
}
