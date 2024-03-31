<?php

namespace Ahoy;

use Ahoy\Store;

class Tracker
{
    protected Store $store;
    protected array $request;
    protected ?string $visit_token;
    protected ?string $visitor_token;
    protected array $options;
    protected array $visit_properties;
    protected bool $are_bots_blocked;
    public bool $is_excluded;

    public function __construct(array $options = [])
    {
        $this->store            = new Store($this);
        $this->request          = $_REQUEST;
        $this->visit_token      = $options['visit_token'] ?? null;
        $this->visit_properties = [];
        $this->are_bots_blocked = $options['block_bots'] ?? true;
        $this->options          = $options;
    }

    public function track(string $name = '', array $properties = [], array $options = []): bool
    {
        if ($this->isExcluded()) {
            Ahoy::log("Event excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->getVisitToken(),
            'user_id'       => $this->getUserId(),
            'name'          => $name,
            'properties'    => $properties,
            'time'          => $this->getTrustedTime($options['time'] ?? 0),
        ];

        $this->store->trackEvent($data);
        return true;
    }

    // TODO: add deferred tracking
    // TODO: add request geocoding
    public function trackVisit(int $started_at = null): bool
    {
        if ($this->isExcluded()) {
            Ahoy::log("Visit excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->getVisitToken(),
            'visitor_token' => $this->getVisitorToken(),
            'user_id'       => $this->getUserId(),
            'started_at'    => $this->getTrustedTime($started_at)
        ];

        $data = array_merge($data, $this->getVisitProperties());
        $this->store->trackVisit($data);
        return true;
    }

    public function getTrustedTime(int $ts = null): int
    {
        if (!$ts) {
            return time();
        }

        $is_ts_current = strtotime('-1 minute') <= $ts && $ts <= time();

        if ($this->fromApi() && !$is_ts_current) {
            return time();
        }

        return $ts;
    }

    public function getVisitProperties(): array
    {
        if ($this->visit_properties) {
            return $this->visit_properties;
        }

        if ($this->request) {
            $visit_properties = new VisitProperties();
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
        return $this->store->generateId();
    }

    private function fromApi(): bool
    {
        return (bool) ($this->options['api'] ?? false);
    }

    private function isExcluded(): bool
    {
        $this->is_excluded ??= $this->store->isExcluded();
        return $this->is_excluded;
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
            $visit_token ??= $_POST['visit_token'] ?? null;
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
            $visitor_token ??= $_POST['visitor_token'] ?? null;
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
}
