<?php

namespace App\Kitano\ProjectManager\PseudoConsole;

use App\Events\ConsoleMessageEvent;
use App\Kitano\ProjectManager\Traits\ProjectLogger;

class Console
{
    /**
     * Emit and log a message for pseudo-console
     *
     * @param string      $msg    Message to broadcast
     * @param null|string $type   Type: info|success|error
     */
    public static function broadcast($msg, $type = null)
    {
        ProjectLogger::addEntry($msg);

        if (isset($type)) {
            $msg = json_encode([
                'type' => $type,
                'message' => $msg,
            ]);
        }

        broadcast(new ConsoleMessageEvent($msg));
    }
}
