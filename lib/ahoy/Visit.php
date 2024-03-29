<?php

namespace Ahoy;

class Visit
{
    public \DateTime $started_at;

    public function __construct(array $data = [])
    {
    }

    public function save()
    {
    }

    public static function last(): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $result = $wpdb->get_row("SELECT * FROM {$tblName} ORDER BY id DESC LIMIT 1");
        return $result;
    }
}
