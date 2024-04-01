<?php

namespace Ahoy;

use DeviceDetector\DeviceDetector;

class VisitProperties
{
    protected array $request;

    public function __construct(array $request)
    {
        $this->request = $request;
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
        $uri = parse_url($_SERVER['HTTP_REFERER'] ?? null);
        return ['referring_domain' => $uri['host'] ?? null];
    }

    private function getTechProperties(): array
    {
        $dd = new DeviceDetector($_SERVER['HTTP_USER_AGENT'] ?? '');
        $dd->parse();

        return [
            'browser'       => $dd->getClient('name'),
            'os'            => $dd->getOs('name'),
            'device_type'   => $dd->getDeviceName(),
        ];
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
