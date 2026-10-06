<?php
declare(strict_types=1);

use Operaattori\Finvoice;

function test_parses_transmission_details(): void
{
    $raw = file_get_contents(dirname(__DIR__) . '/fixtures/messages/finvoice_valid.xml');
    $finvoice = Finvoice::fromString($raw);
    assert_same('MSG-2001', $finvoice->messageId());
    assert_same('003723456780', $finvoice->senderOvt());
    assert_same('MAOPFIH1', $finvoice->fromOperator());
    assert_same('003712345671', $finvoice->recipientOvt());
    assert_same('VLASFIH2', $finvoice->toOperator());
}

function test_parses_amounts_and_dates(): void
{
    $raw = file_get_contents(dirname(__DIR__) . '/fixtures/messages/finvoice_valid.xml');
    $finvoice = Finvoice::fromString($raw);
    assert_same(1550.00, $finvoice->grossTotal());
    assert_same('2026-10-01', $finvoice->invoiceDate());
    assert_same('2026-10-15', $finvoice->dueDate());
    assert_same('FI1011112222333344', $finvoice->sellerIban());
    assert_same('12506', $finvoice->reference());
}
