<?php

register_activation_hook(__DIR__ . '/ahoy.php', 'ahoy_activation');
register_uninstall_hook(__DIR__ . '/ahoy.php', 'ahoy_uninstall');

function ahoy_activation(): void
{
    global $wpdb;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $charset_collate = $wpdb->get_charset_collate();

    // visits table
    $visits_table_name = $wpdb->prefix . "ahoy_visits";
    $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$visits_table_name} (
            `id`                BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `visit_token`       VARCHAR(255) UNIQUE,
            `visitor_token`     VARCHAR(255),
            `user_id`           BIGINT,
            `ip`                VARCHAR(255),
            `user_agent`        TEXT,
            `referrer`          TEXT,
            `referring_domain`  VARCHAR(255),
            `landing_page`      TEXT,
            `browser`           VARCHAR(255),
            `os`                VARCHAR(255),
            `device_type`       VARCHAR(255),
            `country`           VARCHAR(255),
            `region`            VARCHAR(255),
            `city`              VARCHAR(255),
            `latitude`          DECIMAL(11,7),
            `longitude`         DECIMAL(11,7),
            `utm_source`        VARCHAR(255),
            `utm_medium`        VARCHAR(255),
            `utm_term`          VARCHAR(255),
            `utm_content`       VARCHAR(255),
            `utm_campaign`      VARCHAR(255),
            `app_version`       VARCHAR(255),
            `os_version`        VARCHAR(255),
            `platform`          VARCHAR(255),
            `started_at`        DATETIME,
            INDEX `visitor_token_started_at_idx` (`visitor_token`, `started_at`)
        ) {$charset_collate}
    SQL;
    dbDelta($sql);

    // events table
    $events_table_name = $wpdb->prefix . "ahoy_events";
    $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS {$events_table_name} (
            `id`                BIGINT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `visit_id`          BIGINT,
            `user_id`           BIGINT,
            `name`              VARCHAR(255),
            `properties`        TEXT,
            `time`              DATETIME,
            INDEX `name_time_idx` (`name`, `time`),
            FOREIGN KEY (`visit_id`) REFERENCES `{$visits_table_name}`(`id`) ON DELETE CASCADE
        ) {$charset_collate}
    SQL;
    dbDelta($sql);
}

function ahoy_uninstall(): void
{
}
