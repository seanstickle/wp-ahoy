<?php

namespace Ahoy;

class Ahoy
{
    const VISIT_DURATION = 4 * 60 * 60; // 4 hours
    const REST_API_ONLY = false;

    public static function log(string $message): void
    {
        error_log("[ahoy] $message");
    }
}
