<?php
declare(strict_types = 1);

namespace Warehouse\Example;

use Warehouse\Domain\Model\Repository\Sql\MappingInterface;

final class UserMapping implements MappingInterface {
  /**
   * @var array<string, string>
   */
  private array $map = [
    'id'    => 'id',
    'name'  => 'name',
    'email' => 'email'
  ];

  public function getEntityClassName(): string {
    return UserEntity::class;
  }

  public function getTableName(): string {
    return 'users';
  }

  public function getIdColumnName(): string {
    return 'id';
  }

  public function fieldExists(string $fieldName): bool {
    return array_key_exists($fieldName, $this->map);
  }

  public function getColumnName(string $fieldName): string {
    return $this->map[$fieldName];
  }
}
