<?php

declare(strict_types=1);

namespace App\Notification\Domain\Enum;

enum NotificationType: string
{
    case WELCOME = 'welcome';
    case PASSWORD_RESET = 'password_reset';
}
