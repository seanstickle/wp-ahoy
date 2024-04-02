<?php

namespace Ahoy;

class Event
{
    protected object $visit;
    protected int $user_id;
    protected string $name;
    protected array $properties;
    protected float $time = 0;

    public function __construct(array $data = [])
    {
        $this->user_id      = $data['user_id'];
        $this->name         = $data['name'];
        $this->properties   = $data['properties'];
    }

    public function setVisit(object $visit): void
    {
        $this->visit = $visit;
    }

    public function setTime(float $time): void
    {
        $this->time = $time;
    }

    public function save(): int|bool
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_events';

        $now = \DateTime::createFromFormat("U.u", $this->time);

        $result = $wpdb->insert($tblName, [
            'visit_id'      => $this->visit->id,
            'user_id'       => $this->user_id,
            'name'          => $this->name,
            'properties'    => json_encode($this->properties),
            'time'          => $now->format("Y-m-d H:i:s.u"),
        ]);
        return $result;
    }

    public static function last(): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_events';
        $result = $wpdb->get_row("SELECT * FROM {$tblName} ORDER BY id DESC LIMIT 1");

        if (!$result) return null;

        return (object) [
            'id'            => $result->id,
            'visit_id'      => $result->visit_id,
            'user_id'       => $result->user_id,
            'name'          => $result->name,
            'properties'    => json_decode($result->properties ?? ''),
            'time'          => $result->time,
        ];
    }
}
