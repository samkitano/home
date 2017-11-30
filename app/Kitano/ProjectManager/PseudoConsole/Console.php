<?php

namespace App\Kitano\ProjectManager\PseudoConsole;

use App\Events\ConsoleMessageEvent;
use App\Kitano\ProjectManager\Traits\ProjectLogger;

class Console
{
    /**
     * Send message to pseudo-console
     *
     * @param string        $msg
     * @param bool|string   $verbose
     * @param string|null   $type
     */
    public function write($msg, $verbose = true, $type = null)
    {
        if (($type === null) && (! is_bool($verbose))) {
            $type = $verbose;
            $verbose = true;
        }

        ProjectLogger::addEntry($msg);

        if ($verbose) {
            if (isset($type)) {
                $msg = json_encode([
                    'type' => $type,
                    'message' => $msg,
                ]);
            }

            static::broadcast($msg);
        }
    }

    public static function broadcast($msg, $type = null)
    {
        if (isset($type)) {
            $msg = json_encode([
                'type' => $type,
                'message' => $msg,
            ]);
        }

        broadcast(new ConsoleMessageEvent($msg));
    }
}
