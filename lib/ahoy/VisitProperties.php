<?php

namespace Ahoy;

use DeviceDetector\DeviceDetector;

class VisitProperties
{
    public array $request;
    public ?string $referrer;
    public ?string $landingPage;
    public ?string $user_agent;

    public function __construct()
    {
        $this->request      = $_REQUEST ?? [];
        $this->referrer     = $_SERVER['HTTP_REFERER'] ?? null;
        $this->landingPage  = $_SERVER['REQUEST_URI'] ?? null;
        $this->user_agent   = $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    public function toArray(): array
    {
        return array_merge(
            $this->getRequestProperties(),
            $this->getTechProperties(),
            $this->getTrafficProperties(),
            $this->getUtmProperties()
        );
    }

    private function getUtmProperties(): array
    {
        $properties = [];

        $utm = [
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign'
        ];

        foreach ($utm as $key) {
            $properties[$key] = $this->request[$key] ?? null;
        }

        return $properties;
    }

    private function getTrafficProperties(): array
    {
        $uri = parse_url($this->referrer);

        $properties = [
            'referring_domain' => $uri['host'] ?? null
        ];

        return $properties;
    }

    private function getTechProperties(): array
    {
        $dd = new DeviceDetector($this->user_agent);
        $dd->parse();

        $properties = [
            'browser'       => $dd->getClient('name'),
            'os'            => $dd->getOs('name'),
            'device_type'   => $dd->getDeviceName(),
        ];

        return $properties;
    }

    // TODO: add optional IP masking for GDPR compliance
    private function getRequestProperties(): array
    {
        $properties = [
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent'    => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referrer'      => $_SERVER['HTTP_REFERER'] ?? null,
            'landing_page'  => $_SERVER['REQUEST_URI'] ?? null,
        ];

        $properties['user_agent'] = $this->ensureUtf8($properties['user_agent']);
        return $properties;
    }

    private function ensureUtf8(string|null $string): string|null
    {
        if (!$string) return null;
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
    }
}
