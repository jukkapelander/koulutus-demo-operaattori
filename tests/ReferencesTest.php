<?php
declare(strict_types=1);

use Operaattori\References;

function test_domestic_reference_checksum(): void
{
    assert_true(References::domesticValid('12506'));
    assert_true(References::domesticValid('154008'));
    assert_true(!References::domesticValid('12507'));
}

function test_short_reference_is_invalid(): void
{
    assert_true(!References::domesticValid('12'));
}
