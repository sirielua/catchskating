<?php

namespace App\TelegramBot\Helpers;

class MarkdownHelper
{
    public static function escape(string $string): string
    {
        return trim(
            str_replace('_', '\\_', 
                preg_replace('/[^\w_-]/ius', ' ', $string)
            )
        );
    }
}
