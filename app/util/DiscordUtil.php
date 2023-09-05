<?php
declare(strict_types=1);

namespace Mcp\Util;

class DiscordUtil
{
    public static function sendMessageToWebhook($webhook, $title, $message): void
    {
        $rawMessage = file_get_contents("data/discordMessage.json");
        $rawMessage = str_replace("%%message%%", $message, $rawMessage);
        $rawMessage = str_replace("%%title%%", $title, $rawMessage);

        $options = [
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'timeout' => 3,
                'content' => $rawMessage
            ]
        ];

        file_get_contents($webhook, false, stream_context_create($options));
    }
}
