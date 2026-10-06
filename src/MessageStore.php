<?php
declare(strict_types=1);

namespace Operaattori;

/** Tallentaa raa'an sanoman levylle arkistointia varten. */
final class MessageStore
{
    public function store(string $messageId, string $raw): string
    {
        $dir = Config::storagePath() . '/messages';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        // Tiedostonimi lähettäjän MessageId:stä, jotta arkistosta löytää sanoman alkuperäisellä tunnuksella.
        $path = $dir . '/' . $messageId . '.xml';
        file_put_contents($path, $raw);
        return $path;
    }
}
