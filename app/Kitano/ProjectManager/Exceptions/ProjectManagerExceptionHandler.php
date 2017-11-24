<?php

namespace App\Kitano\ProjectManager\Exceptions;

use Exception;
use App\Exceptions\Handler;
use App\Kitano\ProjectManager\Traits\ProjectLogger;

class ProjectManagerExceptionHandler extends Handler
{
    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof ProjectManagerException) {
            // save log
            ProjectLogger::addEntry($exception->getMessage());
            ProjectLogger::saveErrorLog(env('SITES_DIR'));
        }

        parent::report($exception);
    }
}
