<?php

namespace Ahoy;

use Ahoy\Ahoy;
use Ahoy\Event;
use Ahoy\Visit;
use Ramsey\Uuid\Uuid;

class Store
{
    public ?Visit $visit;
    public bool $server_side_visits = true;

    public ?string $visitToken;
    public ?string $visitorToken;

    public function __construct(string $visitorToken = null, string $visitToken = null)
    {
        $this->visit = null;
        $this->visitorToken = $visitorToken;
        $this->visitToken = $visitToken;
    }

    public function track_visit(array $data = []): bool
    {
        $this->visit = new Visit($data);
        $this->visit->save(); // TODO: handle unique constraint exception
        return true;
    }

    public function visit_or_create(array $data =  []): Visit
    {
        if (!$this->visit && $this->server_side_visits) {
            $this->track_visit(['started_at' => $data['started_at']]);
        }

        return $this->getVisit();
    }

    public function track_event(array $data = []): bool
    {
        $visit = $this->visit_or_create(['started_at' => $data['time']]);

        if (!$visit) {
            Ahoy::log("Event excluded since visit not created: {$data['visit_token']}");
            return false;
        }

        $event = new Event($data);
        $event->visit = $visit;
        $event->time = max($visit->started_at, $event->time);
        $event->save(); // TODO: handle unique constraint exception
        return true;
    }

    public function getVisit(): Visit
    {
        if ($this->visit) {
            return $this->visit;
        }

        if ($this->visitToken) {
            $this->visit = Visit::find_by_visit_token($this->visitToken);
            return $this->visit;
        }

        if (Ahoy::COOKIES && $this->visitorToken) {
            $this->visit = Visit::find_by_visitor_token($this->visitorToken);
            return $this->visit;
        }

        $this->visit = null;
        return $this->visit;
    }

    public function is_excluded(): bool
    {
        return Ahoy::BLOCK_BOTS && $this->is_bot();
    }

    public function generate_id(): string
    {
        return Uuid::uuid4()->toString();
    }

    // TODO:implement bot check
    public function is_bot(): bool
    {
        return false;
    }
}
