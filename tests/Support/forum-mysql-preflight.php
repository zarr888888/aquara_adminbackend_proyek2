<?php

use Tests\Support\ForumMysqlTarget;

require dirname(__DIR__, 2).'/vendor/autoload.php';

try {
    $target = ForumMysqlTarget::inspect();
    echo json_encode($target['summary'], JSON_PRETTY_PRINT).PHP_EOL;
    echo 'CONFIG_ONLY: no providers booted; no database connection attempted.'.PHP_EOL;
} catch (Throwable $error) {
    echo 'PREFLIGHT_BLOCKED: '.get_class($error).'; no connection attempted. Review configuration internally.'.PHP_EOL;
    exit(1);
}
