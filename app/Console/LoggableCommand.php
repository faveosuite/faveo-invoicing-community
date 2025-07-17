<?php

namespace App\Console;

use Exception;
use Illuminate\Console\Command;
use Logger;

/**
 * Abstract class for commands that need cron logging.
 *
 * To use this, extend this class and implement the `handleAndLog()` method.
 */
abstract class LoggableCommand extends Command
{
    /**
     * Cron log instance.
     *
     * @var mixed|null
     */
    protected $log;

    /**
     * Final handle method that wraps execution with logging.
     *
     * @return void
     *
     * @throws Exception
     */
    final public function handle(): void
    {
        if (! method_exists($this, 'handleAndLog')) {
            throw new Exception(
                sprintf(
                    'Missing required method handleAndLog() in %s. See App\Console\LoggableCommand documentation.',
                    static::class
                )
            );
        }

        $this->info("Starting: {$this->signature}");

        try {
            $this->log = Logger::cron($this->signature, $this->description);

            $this->laravel->call([$this, 'handleAndLog']);

            if ($this->log) {
                Logger::cronCompleted($this->log->id);
            }

            $this->info("Finished successfully: {$this->signature}");
        } catch (Exception $e) {
            if ($this->log) {
                Logger::cronFailed($this->log->id, $e);
            }

            $this->error(
                sprintf(
                    "\nCommand failed: %s\nError: %s\nFile: %s (%d)\n\nTrace:\n%s",
                    $this->signature,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                )
            );
        }
    }
}
