<?php
declare(strict_types=1);

namespace Mcp\Cron;

use DateInterval;
use DateTime;
use Mcp\Mcp;

abstract class CronJob {

    protected Mcp $app;
    private Frequency $freq;

    public function __construct(Mcp $app, Frequency $freq)
    {
        $this->app = $app;
        $this->freq = $freq;
    }

    public function getNextRun(int $lastRun)
    {
        $prevDate = getdate($lastRun);
        $res = new DateTime('@'.$lastRun);
        switch($this->freq) {
            case Frequency::EACH_MINUTE:
                $res->add(DateInterval::createFromDateString('1 minute'));
                break;
            case Frequency::HOURLY:
                $res->add(DateInterval::createFromDateString('1 hour'));
                break;
            case Frequency::DAILY:
                $res->add(DateInterval::createFromDateString('1 day'));
                $res->setTime(0, 0, 0);
                break;
            case Frequency::WEEKLY:
                $res->add(DateInterval::createFromDateString('1 week'));
                break;
            case Frequency::MONTHLY:
                $res->setDate($prevDate['year'] + ($prevDate['mon'] == 12 ? 1 : 0), $prevDate['mon'] == 12 ? 1 : $prevDate['mon'] + 1, 1);
                break;
            case Frequency::YEARLY:
                $res->setDate($prevDate['year'] + 1, 1, 1);
                break;
            default: break;
        }

        return $res->getTimestamp();
    }

    abstract public function run(): bool;
}
