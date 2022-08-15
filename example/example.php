<?php
declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use Warehouse\Example\UserEntity;
use Warehouse\Example\UserMapping;
use Warehouse\Domain\Model\Repository\Filter;
use Warehouse\Infrastructure\Model\Repository\Sql\SqlFilterProcessor;
use Warehouse\Infrastructure\Model\Repository\Sql\SqlRepository;
use Warehouse\Infrastructure\Model\Repository\Sql\SqlSortProcessor;


$pdo = new PDO('sqlite::memory:');
$pdo->exec(
  <<<SQL
    CREATE TABLE "users" (
      "id" INTEGER PRIMARY KEY AUTOINCREMENT,
      "name" TEXT NOT NULL,
      "email" TEXT NOT NULL
    );
  SQL
);

$userMapping = new UserMapping();

$repository = new SqlRepository(
  new SqlFilterProcessor($userMapping),
  new SqlSortProcessor(),
  $userMapping
);

$user = $repository->add(new UserEntity(null, 'John Doe', 'jdoe@example.com'));

echo 'Load user entity:', PHP_EOL;
var_dump($repository->find($user->getId())->toArray());
echo PHP_EOL;

echo 'List all names containing "D":', PHP_EOL;
$filter = new Filter();
$filter->field('name')->contains('D');
var_dump($repository->findBy($filter)->toArray());
echo PHP_EOL;

echo 'List all users with "id" less than 6:', PHP_EOL;
$filter = new Filter();
$filter->field('id')->isLessThan(6);
var_dump($repository->findBy($filter)->toArray());
echo PHP_EOL;
