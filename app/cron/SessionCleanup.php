<?php
declare(strict_types=1);

namespace Mcp\Cron;

class SessionCleanup extends CronJob
{
    public function __construct(\Mcp\Mcp $app)
    {
        parent::__construct($app, Frequency::HOURLY);
    }

    public function run(): bool
    {
        error_log("Session GC");
        session_start();
        session_gc();
        return true;
    }
}
