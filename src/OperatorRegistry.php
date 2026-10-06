<?php
declare(strict_types=1);

namespace Operaattori;

use PDO;

final class OperatorRegistry
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @return array<string,mixed>|null */
    public function findOperator(string $operatorId): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM operators WHERE operator_id = :id');
        $statement->execute([':id' => $operatorId]);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    /** @return array<string,mixed>|null */
    public function findParty(string $ovt): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM parties WHERE ovt = :ovt');
        $statement->execute([':ovt' => $ovt]);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }
}
