<?php

namespace Ahoy;

use DeviceDetector\DeviceDetector;

class VisitProperties
{
    protected array $request;
    protected string $referrer;
    protected string $landing_page;
    protected string $user_agent;
    protected string $ip;

    public function __construct(array $request)
    {
        $this->request = $request;
        $this->referrer = $request['referrer'] ?? $_SERVER['HTTP_REFERER'] ?? '';
        $this->landing_page = $this->getLandingPage();
        $this->user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $this->ip = $_SERVER['REMOTE_ADDR'] ?? '';
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
        $query = parse_url($this->landing_page, PHP_URL_QUERY) ?? '';
        parse_str($query, $landing_params);

        $properties = [];

        $utm = [
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign'
        ];

        foreach ($utm as $key) {
            $properties[$key] = $this->request[$key] ?? $landing_params[$key] ?? null;
        }

        return $properties;
    }

    private function getTrafficProperties(): array
    {
        return [
            'referring_domain' => parse_url($this->referrer, PHP_URL_HOST),
        ];
    }

    private function getTechProperties(): array
    {
        $dd = new DeviceDetector($this->user_agent);
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
            'ip'            => $this->ip,
            'user_agent'    => $this->user_agent,
            'referrer'      => $this->referrer,
            'landing_page'  => $this->landing_page,
        ];

        $properties['user_agent'] = $this->ensureUtf8($properties['user_agent']);
        return $properties;
    }

    private function ensureUtf8(string|null $string): string|null
    {
        if (!$string) return null;
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
    }

    private function getLandingPage(): string
    {
        if (isset($this->request['landing_page'])) {
            return $this->request['landing_page'];
        }

        $is_https = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $protocol = $is_https ? 'https://' : 'http://';
        $full_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $full_url;
    }
}
