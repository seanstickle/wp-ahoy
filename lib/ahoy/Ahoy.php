<?php

namespace Ahoy;

class Ahoy
{
    const bool COOKIES = true;
    const int VISIT_DURATION = 4 * 60 * 60; // 4 hours
    const bool BLOCK_BOTS = true;

    public static function log(string $message): void
    {
        error_log("[ahoy] $message");
    }
}
