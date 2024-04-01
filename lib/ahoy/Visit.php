<?php

namespace Ahoy;

class Visit
{
    const VISIT_DURATION = 4 * 60 * 60; // 4 hours

    // id
    public int $id;

    // visit + visitor params
    protected ?string $visit_token = null;
    protected ?string $visitor_token = null;
    protected ?int $user_id = null;

    // request params
    protected ?string $ip = null;
    protected ?string $user_agent = null;
    protected ?string $referrer = null;
    protected ?string $landing_page = null;

    // traffic params
    protected ?string $referring_domain = null;

    // utm params
    protected ?string $utm_source = null;
    protected ?string $utm_medium = null;
    protected ?string $utm_term = null;
    protected ?string $utm_content = null;
    protected ?string $utm_campaign = null;

    // tech params
    protected ?string $browser = null;
    protected ?string $os = null;
    protected ?string $device_type = null;

    // TODO: implement geo params
    //
    // public ?string $country = null;
    // public ?string $region = null;
    // public ?string $city = null;
    // public ?float $latitude = null;
    // public ?float $longitude = null;

    // TODO: implement app params
    //
    // public ?string $platform = null;
    // public ?string $app_version = null;
    // public ?string $os_version = null;

    // timestamp
    public float $started_at;

    public function __construct(array $data = [])
    {
        // visit + visitor params
        $this->visit_token      = $data['visit_token'];
        $this->visitor_token    = $data['visitor_token'];
        $this->user_id          = $data['user_id'];

        // request params
        $this->ip               = $data['ip'] ?? null;
        $this->user_agent       = $data['user_agent'] ?? null;
        $this->referrer         = $data['referrer'] ?? null;
        $this->referring_domain = $data['referring_domain'] ?? null;
        $this->landing_page     = $data['landing_page'] ?? null;

        // traffic params
        $this->utm_source       = $data['utm_source'] ?? null;
        $this->utm_medium       = $data['utm_medium'] ?? null;
        $this->utm_term         = $data['utm_term'] ?? null;
        $this->utm_content      = $data['utm_content'] ?? null;
        $this->utm_campaign     = $data['utm_campaign'] ?? null;

        // tech params
        $this->browser          = $data['browser'] ?? null;
        $this->os               = $data['os'] ?? null;
        $this->device_type      = $data['device_type'] ?? null;

        // timestamp
        $this->started_at       = $data['started_at'];
    }

    public function save()
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';

        $now = \DateTime::createFromFormat("U.u", $this->started_at);

        $result = $wpdb->insert($tblName, [
            // visit + visitor params
            'visit_token'       => $this->visit_token,
            'visitor_token'     => $this->visitor_token,
            'user_id'           => $this->user_id,

            // request params
            'ip'                => $this->ip,
            'user_agent'        => $this->user_agent,
            'referrer'          => $this->referrer,
            'referring_domain'  => $this->referring_domain,
            'landing_page'      => $this->landing_page,

            // traffic params
            'utm_source'        => $this->utm_source,
            'utm_medium'        => $this->utm_medium,
            'utm_term'          => $this->utm_term,
            'utm_content'       => $this->utm_content,
            'utm_campaign'      => $this->utm_campaign,

            // tech params
            'browser'           => $this->browser,
            'os'                => $this->os,
            'device_type'       => $this->device_type,

            // timestamp
            'started_at'        => $now->format("Y-m-d H:i:s.u"),
        ]);

        $this->id = $wpdb->insert_id;
        return $result;
    }

    public static function last(): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $result = $wpdb->get_row("SELECT * FROM {$tblName} ORDER BY id DESC LIMIT 1");
        return $result;
    }

    public static function findByVisitToken(string $visit_token): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$tblName} WHERE visit_token = %s", $visit_token));
        if ($result) {
            $result->started_at = \DateTime::createFromFormat("Y-m-d H:i:s.u", $result->started_at)->format("U.u");
        }
        return $result;
    }

    public static function findByVisitorToken(string $visitor_token): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_visits';
        $sql = <<<SQL
            SELECT * FROM {$tblName}
            WHERE visitor_token = %s
            AND started_at >= %
            ORDER BY started_at DESC
        SQL;

        $ts = microtime(true) - Visit::VISIT_DURATION;
        $now = \DateTime::createFromFormat("U.u", $ts);

        $result = $wpdb->get_row($wpdb->prepare(
            $sql,
            $visitor_token,
            $now->format("Y-m-d H:i:s.u"),
        ));
        if ($result) {
            $result->started_at = \DateTime::createFromFormat("Y-m-d H:i:s.u", $result->started_at)->format("U.u");
        }
        return $result;
    }
}
