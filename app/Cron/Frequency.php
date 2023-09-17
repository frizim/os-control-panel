<?php
declare(strict_types=1);

namespace Mcp\Cron;

enum Frequency
{
    case YEARLY; // 01.01. of each year
    case MONTHLY; // 1st of each month
    case WEEKLY; // 1 week after last run
    case DAILY; // Next day after last run, at 00:00
    case HOURLY; // One hour after last run
    case EACH_MINUTE; // One minute after last run
}
