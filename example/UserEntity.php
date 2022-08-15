<?php
declare(strict_types = 1);

namespace Warehouse\Example;

use Warehouse\Domain\Model\Repository\EntityInterface;

final class UserEntity implements EntityInterface {
  private string $id;
  private string $name;
  private string $email;

  public function __construct(mixed $id, string $name, string $email) {
    $this->id    = $id;
    $this->name  = $name;
    $this->email = $email
  }

  public function getId(): mixed {
    return $this->id;
  }

  public function getName(): string {
    return $this->name;
  }

  public function getEmail(): string {
    return $this->email;
  }
}
