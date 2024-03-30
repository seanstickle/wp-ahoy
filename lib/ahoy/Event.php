<?php

namespace Ahoy;

class Event
{
    // public Visit $visit;
    public int $user_id;
    public string $name;
    public string $properties;
    public \DateTime $time;

    public function __construct(array $data = [])
    {
        $this->user_id = $data['user_id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->properties = json_encode($data['properties'] ?? []);
        $this->time = $data['time'] ?? new \DateTime();
    }

    public function save(): bool
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_events';
        $result = $wpdb->insert($tblName, [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'properties' => $this->properties,
            'time' => $this->time->format('Y-m-d H:i:s'),
        ]);
        return $result;
    }

    public static function last(): object|null
    {
        global $wpdb;
        $tblName = $wpdb->prefix . 'ahoy_events';
        $result = $wpdb->get_row("SELECT * FROM {$tblName} ORDER BY id DESC LIMIT 1");

        file_put_contents('/Users/seanstickle/Desktop/last.txt', print_r($result, true) . PHP_EOL, FILE_APPEND);

        if (!$result) return null;

        return (object) [
            'name' => $result->name,
            'properties' => json_decode($result->properties ?? ''),
            'user_id' => $result->user_id,
        ];
    }
}
