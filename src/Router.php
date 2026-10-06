<?php
declare(strict_types=1);

namespace Operaattori;

use PDO;

/**
 * Reitittää vastaanotetun sanoman vastaanottajan operaattorille. Reititysosoite (ToIntermediator)
 * luetaan sanomasta. Ks. README "Finvoice ja reititys".
 */
final class Router
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly OperatorRegistry $registry,
    ) {
    }

    /**
     * Määrittää reitityksen tuloksen vastaanotetulle sanomalle.
     *
     * @param array<string,mixed> $message messages-taulun rivi
     * @return array{status:string, to_operator:?string, reason:string}
     */
    public function route(array $message): array
    {
        $recipientOvt = (string) $message['recipient_ovt'];
        $party = $this->registry->findParty($recipientOvt);

        // Vastaanottajan operaattori sanoman välitystiedoista.
        $toOperator = (string) $message['to_operator'];

        if ($party === null) {
            return ['status' => 'routed', 'to_operator' => $toOperator, 'reason' => 'recipient not in registry, routed by message header'];
        }

        return ['status' => 'routed', 'to_operator' => $toOperator, 'reason' => 'ok'];
    }

    public function apply(int $messageId, array $decision): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE messages SET status = :status, to_operator = :to_operator, routed_at = :routed_at WHERE id = :id'
        );
        $statement->execute([
            ':status' => $decision['status'],
            ':to_operator' => $decision['to_operator'],
            ':routed_at' => date('Y-m-d H:i:s'),
            ':id' => $messageId,
        ]);
    }
}
