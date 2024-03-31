<?php

namespace Ahoy;

use Ahoy\Store;

class Tracker
{
    // namespace used to generate v5 UUIDs
    public string $UUID_NAMESPACE = "a82ae811-5011-45ab-a728-569df7499c5f";

    public Store $store;
    public $controller;
    public $request;
    public $visitToken;
    public $user;
    public $options;
    public $visitProperties;

    public bool $is_excluded;

    public function __construct(array $options = [])
    {
        $this->store        = new Store();
        $this->controller   = $options['controller'] ?? null;
        $this->request      = $options['request'] ?? null;
        $this->visitToken   = $options['visit_token'] ?? null;
        $this->user         = $options['user'] ?? null;
        $this->options      = $options;
    }

    public function track(string $name = '', array $properties = [], array $options = []): bool
    {
        if ($this->is_excluded()) {
            Ahoy::log("Event excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->visitToken,
            'user_id'       => $this->user->id ?? null,
            'name'          => $name,
            'properties'    => $properties,
            'time'          => $this->trustedTime($options['time'] ?? 0),
            'event_id'      => $options['id'] ?? $this->generate_id()
        ];

        $this->store->track_event($data);
        return true;
    }

    // TODO: implement deferred tracking support & queued geocoding
    public function track_visit(): bool
    {
        if ($this->is_excluded()) {
            Ahoy::log("Visit excluded");
            return true;
        }

        $data = [
            'visit_token'   => $this->visitToken,
            'visitor_token' => $this->store->visitorToken,
            'user_id'       => $this->user->id ?? null,
            'started_at'    => $this->trustedTime($options['started_at'] ?? 0)
        ];

        $data = array_merge($data, $this->getVisitProperties());
        $this->store->track_visit($data);
        return true;
    }

    public function trustedTime(int $ts = null): int
    {
        if (!$ts) {
            return time();
        }

        $is_ts_current = strtotime('-1 minute') <= $ts && $ts <= time();

        if ($this->from_api() && !$is_ts_current) {
            return time();
        }

        return $ts;
    }

    public function getVisitProperties(): array
    {
        if ($this->visitProperties) {
            return $this->visitProperties;
        }

        if ($this->request) {
            $visitProperties = new VisitProperties($this->request, $this->from_api());
            $this->visitProperties = $visitProperties->toArray();
            return $this->visitProperties;
        }

        $this->visitProperties = [];
        return $this->visitProperties;
    }

    /**
     *
     * private functions
     *
     */

    private function generate_id(): string
    {
        return $this->store->generate_id();
    }

    private function from_api(): bool
    {
        return (bool) ($this->options['api'] ?? false);
    }

    private function is_excluded(): bool
    {
        $this->is_excluded ??= $this->store->is_excluded();
        return $this->is_excluded;
    }
}
