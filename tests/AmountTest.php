<?php
declare(strict_types=1);

use Operaattori\Amount;

function test_amount_parses_plain_decimal(): void
{
    assert_same(1250.00, Amount::parse('1250.00'));
    assert_same(0.0, Amount::parse(''));
}

function test_amount_parses_integer_like(): void
{
    assert_same(500.0, Amount::parse('500'));
}
