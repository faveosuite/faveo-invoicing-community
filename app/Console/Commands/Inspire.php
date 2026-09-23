<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\LoggableCommand;
use Illuminate\Foundation\Inspiring;

class Inspire extends LoggableCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'inspire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     */
    public function handleAndLog(): void
    {
        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
    }
}
