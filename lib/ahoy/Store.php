<?php

namespace Ahoy;

use Ramsey\Uuid\Uuid;
use Ahoy\Visit;
use Ahoy\Event;

class Store
{
    public Visit $visit;
    public bool $server_side_visits = true;

    public function __construct()
    {
    }

    public function track_visit(array $data)
    {
        try {
            $this->visit = (new Visit($data))->save();
        } catch (\Exception $e) {
            throw $e; // TODO: unless it's a duplicate
            if (isset($this->visit)) unset($this->visit);
        }
    }

    public function track_event(array $data)
    {
        $visit = true; // $this->visit_or_create(started_at: $data['time']);

        if ($visit) {
            $event = new Event($data);
            // $event->visit = $visit;
            // $event->time = max($visit->started_at, $event->time);
            try {
                $event->save();
            } catch (\Exception $e) {
                throw $e; // TODO: unless it's a duplicate
            }
        } else {
            error_log("[ahoy] Event excluded since visit not created: {$data['visit_token']}");
        }
    }

    // if we don't have a visit, let's try to create one first
    private function visit_or_create(\DateTime $started_at = null): Visit
    {
        if (!isset($this->visit) && $this->server_side_visits) {
            $this->track_visit(['started_at' => $started_at]);
        }
        return $this->visit();
    }

    public function visit(): Visit
    {
        if (!isset($this->visit)) {
            if ($existing_visit_token) {
            } else if (!$cookies && $visitor_token) {
            } else {
                $this->visit = null;
            }
        }

        return $this->visit;
    }

    public function exclude(): bool
    {
        return false;
    }

    public function generate_id(): string
    {
        return Uuid::uuid4();
    }
}
