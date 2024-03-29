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
    public $visit_token;
    public $user;
    public $options;

    public bool $exclude;

    public function __construct(array $options = [])
    {
        $this->store = new Store();
        $this->controller = $options['controller'] ?? null;
        $this->request = $options['request'] ?? null;
        $this->visit_token = $options['visit_token'] ?? null;
        $this->user = $options['user'] ?? null;
        $this->options = $options;
    }

    public function track(string $name = '', array $properties = [], array $options = []): bool
    {
        if ($this->exclude()) {
            $this->debug("Event excluded");
        } else {
            $data = [
                'visit_token' => $this->visit_token,
                'user_id' => $this->user->id ?? 0,
                'name' => $name,
                'properties' => $properties,
                'time' => $this->trustedTime($options['time'] ?? 0),
                'event_id' => $options['id'] ?? $this->generate_id()
            ];
            $data = array_filter($data, fn ($v) => $v);
            $this->store->track_event($data);
        }
        return true;
    }

    private function generate_id(): string
    {
        return $this->store->generate_id();
    }


    private function trustedTime(int $time = null): \DateTime
    {
        $current = strtotime('-1 minute') <= $time && $time <= time();
        if (!$time || ($this->api() && !$current)) {
            return new \DateTime();
        } else {
            return (new \DateTime)->setTimestamp($time);
        }
    }

    private function api(): bool
    {
        return (bool) $this->options['api'];
    }

    private function debug(string $message): void
    {
        error_log($message);
    }

    private function exclude(): bool
    {
        if (!isset($this->exclude)) {
            $this->exclude = $this->store->exclude();
        }
        return $this->exclude;
    }
}
