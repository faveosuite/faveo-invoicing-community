<?php

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
     *
     * @return mixed
     */
    public function handleAndLog()
    {
        $this->comment(PHP_EOL.Inspiring::quote().PHP_EOL);
    }
}
