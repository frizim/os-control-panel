<?php
declare(strict_types=1);

namespace Mcp\Api;

use Mcp\RequestHandler;

class CronStarter extends RequestHandler
{

    private const CRONJOBS_INTERNAL = ['SessionCleanup'];

    public function get(): void
    {
        if ($this->app->config('cron-restriction') == 'key' && !(isset($_GET['key']) && hash_equals($this->app->config('cron-key'), $_GET['key']))) {
            http_response_code(403);
            return;
        }

        $cronJobs = array_merge($this->app->config('cronjobs'), $this::CRONJOBS_INTERNAL);

        $cronStatement = $this->app->db()->prepare('SELECT Name,LastRun FROM mcp_cron_runs');
        $cronStatement->execute();

        $jobRuns = array();
        while ($row = $cronStatement->fetch()) {
            $jobRuns[$row['Name']] = $row['LastRun'];
        }

        $cronUpdateStatement = $this->app->db()->prepare('REPLACE INTO mcp_cron_runs(Name,LastRun) VALUES (?,?)');
        foreach ($cronJobs as $jobName) {
            $jobClass = "Mcp\\Cron\\".$jobName;
            if (in_array($jobName, $cronJobs)) {
                $job = (new $jobClass($this->app));
                $now = time();
                $nextRun = $job->getNextRun(isset($jobRuns[$jobName]) ? $jobRuns[$jobName] : $now - 60);
                if ($now >= $nextRun && $job->run()) {
                    error_log($now.' <= '.$nextRun);
                    $cronUpdateStatement->execute([$jobName, time()]);
                }
            }
        }
    }
}
