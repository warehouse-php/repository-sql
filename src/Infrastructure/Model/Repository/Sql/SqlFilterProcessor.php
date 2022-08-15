<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

use Doctrine\DBAL\Query\QueryBuilder;
use Warehouse\Domain\Model\Repository\FilterProcessorInterface;

final class SqlFilterProcessor implements FilterProcessorInterface {
  private MappingInterface $mapping;
  private QueryBuilder $queryBuilder;

  public function __construct(MappingInterface $mapping) {
    $this->mapping = $mapping;
  }

  public function setQueryBuilder(QueryBuilder $queryBuilder): static {
    $this->queryBuilder = $queryBuilder;

    return $this;
  }

  public function getQueryBuilder(): QueryBuilder {
    return $this->queryBuilder;
  }

  public function apply(FilterInterface $filter): static {
    foreach ($filter->get() as $fieldName => $filterSpec) {
      if ($this->mapping->fieldExists($fieldName) === false) {
        throw new LogicException(
          sprintf(
            'Field "%s" is not included in the result set',
            $fieldName
          )
        );
      }

      $expr = $this->queryBuilder->expr();

      switch ($filterSpec['filter']) {
        case 'startsWith':
          if ($filterSpec['or'] === true) {
            if ($filterSpec['not'] === true) {
              // OR "{$fieldName}" NOT LIKE '{$filterSpec['value']}%'
              $this->queryBuilder->orWhere(
                $expr->notLike($fieldName, $filterSpec['value'] . '%')
              );

              break;
            }

            // OR "{$fieldName}" LIKE '{$filterSpec['value']}%'
            $this->queryBuilder->orWhere(
              $expr->like($fieldName, $filterSpec['value'] . '%')
            );

            break;
          }

          if ($filterSpec['not'] === true) {
            // AND "{$fieldName}" NOT LIKE '{$filterSpec['value']}%'
            $this->queryBuilder->andWhere(
              $expr->notLike($fieldName, $filterSpec['value'] . '%')
            );

            break;
          }

          // AND "{$fieldName}" LIKE '{$filterSpec['value']}%'
          $this->queryBuilder->andWhere(
            $expr->like($fieldName, $filterSpec['value'] . '%')
          );

          break;
        case 'endsWith':
        case 'contains':
        case 'isNull':
        case 'isEmpty':
        case 'isTrue':
        case 'isFalse':
        case 'isEqualTo':
        case 'isGreaterThan':
        case 'isGreaterThanOrEqualTo':
        case 'isLessThan':
        case 'isLessThanOrEqualTo':
        case 'isBetween':
        case 'inArray':
      }
    }

    return $this;
  }
}
