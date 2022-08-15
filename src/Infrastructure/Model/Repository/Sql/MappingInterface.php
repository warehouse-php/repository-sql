<?php
declare(strict_types = 1);

namespace Warehouse\Infrastructure\Model\Repository\Sql;

interface MappingInterface {
  public function getEntityClassName(): string;

  public function getTableName(): string;

  public function getIdColumnName(): string;

  public function fieldExists(string $fieldName): bool;

  public function getColumnName(string $fieldName): string;
}
