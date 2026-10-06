<?php
declare(strict_types=1);

use Operaattori\Iban;

function test_valid_finnish_iban(): void
{
    assert_true(Iban::valid('FI1011112222333344'));
    assert_true(Iban::valid('FI48 2222 3333 4444 55'));
}

function test_invalid_iban_checksum(): void
{
    assert_true(!Iban::valid('FI1011112222333345'));
}

function test_garbage_is_invalid(): void
{
    assert_true(!Iban::valid('FI00'));
    assert_true(!Iban::valid(' roskaa '));
}
