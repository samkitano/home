<?php

namespace App\Kitano\ProjectManager;

use App\Events\ConsoleMessageEvent;

trait Communicator
{
    /**
     * Send message to pseudo-console
     *
     * @param string        $msg
     * @param bool|string   $verbose
     * @param string|null   $type
     */
    public static function send($msg, $verbose = true, $type = null)
    {
        if (($type === null) && (! is_bool($verbose))) {
            $type = $verbose;
            $verbose = true;
        }

        if ($verbose) {
            if (isset($type)) {
                $msg = json_encode([
                    'type' => $type,
                    'message' => $msg,
                ]);
            }

            broadcast(new ConsoleMessageEvent($msg));
        }
    }
}
