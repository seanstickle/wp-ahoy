<?php

namespace Ahoy;

use Ahoy\Ahoy;
use Ahoy\Event;
use Ahoy\Visit;
use Ramsey\Uuid\Uuid;
use DeviceDetector\DeviceDetector;

class Store
{
    public ?Visit $visit;
    public bool $server_side_visits = true;

    public ?string $visit_token;
    public ?string $visitor_token;
    public string $user_agent;

    protected $tracker;

    public function __construct(Tracker $tracker, string $visitor_token = null, string $visit_token = null)
    {
        $this->tracker = $tracker;
        $this->visit = null;
        $this->visitor_token = $visitor_token;
        $this->visit_token = $visit_token;
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function trackVisit(array $data = []): bool
    {
        $this->visit = new Visit($data);
        $this->visit->save(); // TODO: handle unique constraint exception
        return true;
    }

    public function visitOrCreate(int $started_at = null): Visit
    {
        if (!$this->visit && $this->server_side_visits) {
            $this->tracker->trackVisit($started_at);
        }

        return $this->getVisit();
    }

    // $data = [
    //     'visit_token'   => $this->getVisitToken(),
    //     'user_id'       => $this->getUserId(),
    //     'name'          => $name,
    //     'properties'    => $properties,
    //     'time'          => $this->getTrustedTime($options['time'] ?? 0),
    // ];

    public function trackEvent(array $data = []): bool
    {
        $visit = $this->visitOrCreate(started_at: $data['time']);

        if (!$visit) {
            Ahoy::log("Event excluded since visit not created: {$data['visit_token']}");
            return false;
        }

        $event = new Event($data);
        $event->setVisit($visit);
        $event->setTime(max(
            $visit->started_at,
            $event->getTime()
        ));
        $event->save(); // TODO: handle unique constraint exception
        return true;
    }

    public function getVisit(): Visit
    {
        if ($this->visit) {
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

    public function isExcluded(): bool
    {
        return $this->tracker->areBotsBlocked() && $this->isBot();
    }

    public function generateId(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function isBot(): bool
    {
        $dd = new DeviceDetector($this->user_agent);
        $dd->parse();
        return $dd->isBot();
    }
}
