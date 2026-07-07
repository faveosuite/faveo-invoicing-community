<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Seo\SeoFileGenerator;
use Illuminate\Console\Command;

class GenerateSeoFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seo:generate-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate public/sitemap.xml, robots.txt, and llms.txt';

    /**
     * Execute the console command.
     */
    public function handle(SeoFileGenerator $generator): void
    {
        $generator->generateAll();

        $this->info('seo:generate-files Command Run successfully!');
    }
}
