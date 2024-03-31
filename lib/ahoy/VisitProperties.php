<?php

namespace Ahoy;

class VisitProperties
{

    public array $request;
    public array $params;
    public string $referrer;
    public string $landingPage;

    public function __construct(array $request, bool $api = false)
    {
        $this->request = $request;
        $this->params = $request['params'];
        $this->referrer = $api ? $this->params['referrer'] : $_SERVER['HTTP_REFERER'];
        $this->landingPage = $api ? $this->params['landing_page'] : $_SERVER['REQUEST_URI'];
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

    /**
     *
     * private functions
     *
     */

    private function getUtmProperties(): array
    {
        $landingUri = parse_url($this->landingPage);

        $landingParams = [];

        if (isset($landingUri["query"])) {
            parse_str($landingUri["query"], $landingParams);
        }

        $properties = [];

        $utm = [
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign'
        ];

        foreach ($utm as $key) {
            $properties[$key] = $this->params[$key] ?? $landingParams[$key] ?? null;
        }

        return $properties;
    }

    private function getTrafficProperties(): array
    {
        $uri = parse_url($this->referrer ?? '');

        $properties = [
            'referring_domain' => $uri['host'] ?? null
        ];

        return $properties;
    }

    // TODO: Implement tech_properties
    private function getTechProperties(): array
    {
        $properties = [
            'browser' => '',
            'os' => '',
            'device_type' => ''
        ];

        return $properties;
    }

    private function getRequestProperties(): array
    {
        $properties = [
            'ip' => $this->ip(),
            'user_agent' => $this->ensureUtf8($this->request['user_agent']),
            'referrer' => $this->referrer,
            'landing_page' => $this->landingPage,
            'platform' => $this->params['platform'] ?? null,
            'app_version' => $this->params['app_version'] ?? null,
            'os_version' => $this->params['os_version'] ?? null,
            'screen_height' => $this->params['screen_height'] ?? null,
            'screen_width' => $this->params['screen_width'] ?? null
        ];

        return $properties;
    }

    // TODO: Implement optiopnal IP masking
    private function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    private function ensureUtf8(string $string): string
    {
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string));
    }
}
