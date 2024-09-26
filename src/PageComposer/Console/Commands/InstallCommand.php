<?php

namespace Flobbos\PageComposer\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\Artisan;

class InstallCommand extends GeneratorCommand
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

    public function getStub()
    {
        return;
    }

    public function handle()
    {
        $this->comment('Running migrations.');

        Artisan::call('migrate');

        $this->comment('Running seeder for initial elements.');
        Artisan::call('db:seed', ['--class' => 'Flobbos\\PageComposer\\ElementTableSeeder']);

        $this->comment('Publishing package files');

        Artisan::call('vendor:publish', ['--provider' => 'Flobbos\\PageComposer\\PageComposerServiceProvider']);

        $this->info('Installation completed.');
    }
}
