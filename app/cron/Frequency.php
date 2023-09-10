<?php
declare(strict_types=1);

namespace Mcp\Cron;

enum Frequency
{
    case YEARLY;
    case MONTHLY;
    case WEEKLY;
    case DAILY;
    case HOURLY;
    case EACH_MINUTE;
}
