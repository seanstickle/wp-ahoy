<?php

namespace Ahoy;

class Visit
{
    public string $visit_token;
    public string $visitor_token;
    public int $user_id;
    public string $ip;
    public string $user_agent;
    public string $referrer;
    public string $referring_domain;
    public string $landing_page;
    public string $browser;
    public string $os;
    public string $device_type;
    public string $country;
    public string $region;
    public string $city;
    public float $latitude;
    public float $longitude;
    public string $utm_source;
    public string $utm_medium;
    public string $utm_term;
    public string $utm_content;
    public string $utm_campaign;
    public string $app_version;
    public string $os_version;
    public string $platform;
    public int $started_at;

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

    public static function find_by_visit_token(string $visit_token): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tblName} WHERE visit_token = %s", $visit_token));
        return $result;
    }

    public static function find_by_visitor_token(string $visitor_token): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $sql = <<<SQL
            SELECT * FROM {$tblName}
            WHERE visitor_token = %s
            AND started_at >= %
            ORDER BY started_at DESC
        SQL;
        $result = $wpdb->get_row($wpdb->prepare(
            $sql,
            $visitor_token,
            date("Y-m-d H:i:s", time() - Ahoy::VISIT_DURATION)
        ));
        return $result;
    }
}
