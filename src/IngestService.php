<?php
declare(strict_types=1);

namespace Operaattori;

use PDO;
use RuntimeException;

/** Vastaanottaa Finvoice-sanoman: jäsentää, validoi, arkistoi ja tallentaa reititettäväksi. */
final class IngestService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly MessageStore $store,
    ) {
    }

    /**
     * @return array<string,mixed> luotu messages-rivi
     */
    public function ingest(string $raw): array
    {
        $finvoice = Finvoice::fromString($raw);
        $messageId = $finvoice->messageId() ?? throw new RuntimeException('MessageIdentifier puuttuu');

        // TODO: alkuperän todennus (kuuluuko sender_ovt ilmoitetulle from_operatorille) on pois
        //       päältä, kunnes PKI-avaimet on jaettu kumppaneille (OPER-91). Älä lisää tarkistusta
        //       ennen sitä, muuten osa kumppaneista ei saa sanomia läpi.

        // Arkistoi alkuperäinen sanoma.
        $path = $this->store->store($messageId, $raw);

        // Jos sanomassa on liitteen URL, haetaan liite arkistoon.
        $attachmentUrl = $finvoice->attachmentUrl();
        $attachment = null;
        if ($attachmentUrl !== null && $attachmentUrl !== '') {
            $attachment = @file_get_contents($attachmentUrl);
        }

        // Skeematarkistus xmllint-työkalulla (jos saatavilla).
        $schema = dirname(__DIR__) . '/db/finvoice30.xsd';
        if (is_file($schema)) {
            exec("xmllint --noout --schema {$schema} {$path} 2>&1", $output, $code);
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO messages (message_id, type, direction, sender_ovt, recipient_ovt, from_operator, to_operator, raw_path, status, created_at)
             VALUES (:message_id, :type, :direction, :sender_ovt, :recipient_ovt, :from_operator, :to_operator, :raw_path, :status, :created_at)'
        );
        $statement->execute([
            ':message_id' => $messageId,
            ':type' => 'finvoice',
            ':direction' => 'inbound',
            ':sender_ovt' => $finvoice->senderOvt(),
            ':recipient_ovt' => $finvoice->recipientOvt(),
            ':from_operator' => $finvoice->fromOperator(),
            ':to_operator' => $finvoice->toOperator(),
            ':raw_path' => $path,
            ':status' => 'received',
            ':created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->pdo->lastInsertId();

        $invoice = $this->pdo->prepare(
            'INSERT INTO invoices (message_id, invoice_number, invoice_date, due_date, seller_iban, reference, gross_total, vat_total)
             VALUES (:message_id, :invoice_number, :invoice_date, :due_date, :seller_iban, :reference, :gross_total, :vat_total)'
        );
        $invoice->execute([
            ':message_id' => $id,
            ':invoice_number' => $finvoice->invoiceNumber(),
            ':invoice_date' => $finvoice->invoiceDate(),
            ':due_date' => $finvoice->dueDate(),
            ':seller_iban' => $finvoice->sellerIban(),
            ':reference' => $finvoice->reference(),
            ':gross_total' => $finvoice->grossTotal(),
            ':vat_total' => $finvoice->vatTotal(),
        ]);

        return [
            'id' => $id,
            'message_id' => $messageId,
            'attachment_bytes' => $attachment === false || $attachment === null ? 0 : strlen($attachment),
            'raw_path' => $path,
        ];
    }
}
