<?php

namespace Ahoy;

class Ahoy
{
    const int VISIT_DURATION = 4 * 60 * 60; // 4 hours
    const bool REST_API_ONLY = false;

    public static function log(string $message): void
    {
        error_log("[ahoy] $message");
    }
}
