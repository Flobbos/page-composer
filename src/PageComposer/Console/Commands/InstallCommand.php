<?php

namespace App\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;

class MakeElementCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'pagecomposer:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install PageComposer in your project';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Installation';

    public function handle()
    {
        $this->comment('Running migrations.');

        Artisan::call('migrate');

        $this->comment('Running seeder for initial elements.');
        Artisan::call('db:seed', ['--class' => 'Flobbos\\PageComposer\\Database\\Seeders\\ElementTableSeeder']);

        $this->comment('Publishing package files');

        Artisan::call('vendor:publish', ['--provider' => 'Flobbos\\PageComposer\\PageComposerServiceProvider']);

        $this->info('Installation completed.');
    }
}
