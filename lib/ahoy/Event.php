<?php

namespace Ahoy;

class Event
{
    protected Visit $visit;
    protected int $user_id;
    protected string $name;
    protected string $properties;
    protected int $time = 0;

    public function __construct(array $data = [])
    {
        $this->user_id      = $data['user_id'];
        $this->name         = $data['name'];
        $this->properties   = json_encode($data['properties']);
    }

    public function setVisit(Visit $visit): void
    {
        $this->visit = $visit;
    }

    public function setTime($time): void
    {
        $this->time = $time;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function save(): int|bool
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_events';
        $result = $wpdb->insert($tblName, [
            'visit_id'      => $this->visit->id,
            'user_id'       => $this->user_id,
            'name'          => $this->name,
            'properties'    => $this->properties,
            'time'          => $this->time,
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
