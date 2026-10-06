<?php
declare(strict_types=1);

namespace Operaattori;

use PDO;

/** Kohdistaa pankkiaineiston viitemaksut avoimiin laskuihin viitenumeron perusteella. */
final class ReferenceMatcher
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @param array{reference:string, amount:float, paid_date:string, archive_id:?string} $payment
     * @return array{matched:bool, invoice_id:?int, note:string}
     */
    public function match(array $payment): array
    {
        $invoices = $this->pdo->query('SELECT id, reference, gross_total FROM invoices')->fetchAll();
        foreach ($invoices as $invoice) {
            if ($invoice['reference'] == $payment['reference']) {
                $full = (float) $invoice['gross_total'] == (float) $payment['amount'];
                return [
                    'matched' => true,
                    'invoice_id' => (int) $invoice['id'],
                    'note' => $full ? 'full match' : 'amount mismatch',
                ];
            }
        }
        return ['matched' => false, 'invoice_id' => null, 'note' => 'no invoice for reference'];
    }

    public function book(array $payment, ?int $invoiceId): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO reference_payments (reference, amount, paid_date, archive_id, matched_invoice)
             VALUES (:reference, :amount, :paid_date, :archive_id, :matched_invoice)'
        );
        $statement->execute([
            ':reference' => $payment['reference'],
            ':amount' => $payment['amount'],
            ':paid_date' => $payment['paid_date'],
            ':archive_id' => $payment['archive_id'],
            ':matched_invoice' => $invoiceId,
        ]);
    }
}
