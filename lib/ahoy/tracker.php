<?php

namespace Ahoy;

use DeviceDetector\DeviceDetector;
use Ramsey\Uuid\Uuid;

class Tracker
{
    protected bool $are_bots_blocked;
    public bool $is_excluded;

    protected object $visit;
    protected ?string $visit_token;
    protected ?string $visitor_token;

    protected array $visit_properties;
    protected array $options;

    protected array $request;

    public function __construct(array $options = [])
    {
        $this->visit_token      = $options['visit_token'] ?? null;
        $this->are_bots_blocked = $options['block_bots'] ?? true;
        $this->request          = $options['request'] ?? $_REQUEST;
        $this->options          = $options;
    }

    public function track(string $name = '', array $properties = [], array $options = []): bool
    {
        if ($this->isExcluded()) {
            $this->log("Event excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->getVisitToken(),
            'user_id'       => $this->getUserId(),
            'name'          => $name,
            'properties'    => $properties,
            'time'          => $this->getTrustedTime($options['time'] ?? 0),
        ];

        // get (or create) the visit for this event
        $visit = $this->visitOrCreate(started_at: $data['time']);

        if (!$visit) {
            $this->log("Event excluded since visit not created: {$data['visit_token']}");
            return false;
        }

        $event = new Event($data);
        $event->setVisit($visit);
        $event->setTime(max($visit->started_at, $data['time']));
        $event->save();
        return true;
    }

    public function visitOrCreate(float $started_at = null): object|null
    {
        if (!isset($this->visit)) $this->trackVisit($started_at);
        return $this->getVisit();
    }

    public function getVisit(): object|null
    {
        if (isset($this->visit)) {
            return $this->visit;
        }

        if ($this->visit_token) {
            $this->visit = Visit::findByVisitToken($this->visit_token);
            return $this->visit;
        }

        if ($this->visitor_token) {
            $this->visit = Visit::findByVisitorToken($this->visitor_token);
            return $this->visit;
        }

        $this->visit = null;
        return $this->visit;
    }

    // TODO: add deferred tracking
    // TODO: add request geocoding
    public function trackVisit(float $started_at = null): bool
    {
        if ($this->isExcluded()) {
            $this->log("Visit excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->getVisitToken(),
            'visitor_token' => $this->getVisitorToken(),
            'user_id'       => $this->getUserId(),
            'started_at'    => $this->getTrustedTime($started_at)
        ];

        $data = array_merge($data, $this->getVisitProperties());

        $this->visit = new Visit($data);
        $result = $this->visit->save();

        if ($result === false) {
            global $wpdb;
            if ($wpdb->last_error && stripos($wpdb->last_error, 'duplicate') !== false) {
                unset($this->visit); // unset so the code fetches the correct visit
            }
        }

        return true;
    }

    public function getTrustedTime(float $ts = null): float
    {
        if (!$ts) {
            return microtime(true);
        }

        $is_ts_current = strtotime('-1 minute') <= $ts && $ts <= microtime(true);

        if ($this->fromApi() && !$is_ts_current) {
            return microtime(true);
        }

        return $ts;
    }

    public function getVisitProperties(): array
    {
        if (isset($this->visit_properties)) {
            return $this->visit_properties;
        }

        if ($this->request) {
            $visit_properties = new VisitProperties($this->request);
            $this->visit_properties = $visit_properties->toArray();
            return $this->visit_properties;
        }

        $this->visit_properties = [];
        return $this->visit_properties;
    }

    public function areBotsBlocked(): bool
    {
        return $this->are_bots_blocked;
    }

    /**
     *
     * private functions
     *
     */

    private function getUserId(): int
    {
        return get_current_user_id();
    }

    private function generateId(): string
    {
        return Uuid::uuid4()->toString();
    }

    private function fromApi(): bool
    {
        return (bool) ($this->options['api'] ?? false);
    }

    private function isExcluded(): bool
    {
        $this->is_excluded ??= $this->areBotsBlocked() && $this->isBot();
        return $this->is_excluded;
    }

    private function isBot(): bool
    {
        $dd = new DeviceDetector($_SERVER['HTTP_USER_AGENT'] ?? '');
        $dd->parse();
        return $dd->isBot();
    }

    private function getVisitToken(): string
    {
        $this->visit_token ??= $this->ensureToken($this->visitTokenHelper());
        return $this->visit_token;
    }

    private function getVisitorToken(): string
    {
        $this->visitor_token ??= $this->ensureToken($this->visitorTokenHelper());
        return $this->visitor_token;
    }

    // TODO: add support for anonymity set for GDPR compliance
    // TODO: add support for API_ONLY mode
    private function visitTokenHelper(): string|null
    {
        $visit_token = $this->existingVisitToken();
        $visit_token ??= $this->generateId();
        return $visit_token;
    }

    // TODO: add support for anonymity set for GDPR compliance
    // TODO: add support for API_ONLY mode
    private function visitorTokenHelper(): string|null
    {
        $visitor_token = $this->existingVisitorToken();
        $visitor_token ??= $this->generateId();
        return $visitor_token;
    }

    private function existingVisitToken(): string|null
    {
        // use header when sent from different domain/subdomain with Fetch API
        $visit_token = $_SERVER['Ahoy-Visit'] ?? null;

        // use cookie when not sending through REST API
        if (!$this->fromApi()) {
            $visit_token ??= $_COOKIE['ahoy_visit'] ?? null;
        }

        // use param when sending through REST API
        if ($this->fromApi()) {
            $visit_token ??= $this->request['visit_token'] ?? null;
        }

        return $visit_token;
    }

    private function existingVisitorToken(): string|null
    {
        // use header when sent from different domain/subdomain with Fetch API
        $visitor_token = $_SERVER['Ahoy-Visitor'] ?? null;

        // use cookie when not sending through REST API
        if (!$this->fromApi()) {
            $visitor_token ??= $_COOKIE['ahoy_visitor'] ?? null;
        }

        // use param when sending through REST API
        if ($this->fromApi()) {
            $visitor_token ??= $this->request['visitor_token'] ?? null;
        }

        return $visitor_token;
    }

    private function ensureToken(string $token = ''): string|null
    {
        if (!$token) return null;

        $token = mb_convert_encoding($token, 'UTF-8', mb_detect_encoding($token));
        $token = preg_replace('/[^a-z0-9\-]/i', '', $token);
        return substr($token, 0, 64);
    }

    private static function log(string $message): void
    {
        error_log("[ahoy] $message");
    }
}
