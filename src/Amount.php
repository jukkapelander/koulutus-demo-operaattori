<?php
declare(strict_types=1);

namespace Operaattori;

final class Amount
{
    /**
     * Jäsentää Finvoice-rahamäärän liukuluvuksi. Finvoicessa desimaalierotin voi olla pilkku tai
     * piste, ja tuhaterottimena voi olla välilyönti.
     */
    public static function parse(string $raw): float
    {
        return (float) $raw;
    }
}
