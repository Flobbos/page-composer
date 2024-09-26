<?php

namespace Flobbos\PageComposer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\AliasLoader;

class PageComposerServiceProvider extends ServiceProvider
{

  public function boot()
  {
    //Publish config
    $this->publishes([
      __DIR__ . '/../config/pagecomposer.php' => config_path('pagecomposer.php'),
    ], 'page-composer-config');

    //Publish migrations
    $this->publishes([
      __DIR__ . '/../database/migrations/' => database_path('migrations'),
    ], 'page-composer-migrations');

    //Publishes defaults
    $this->publishes([
      __DIR__ . '/Models' => app_path('/Models')
    ], 'page-composer-models');

    //Add Laravel CM routes
    $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    //Add views depending on the css framework setting in config
    $this->loadViewsFrom(__DIR__ . '/../resources/views', 'page-composer');
    //Add language files
    $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'page-composer');
    //Load migrations
    $this->loadMigrationsFrom(__DIR__ . '/../database/migrations', 'page-composer');
  }

  /**
   * Register the service provider.
   */
  public function register()
  {
    //Merge config
    $this->mergeConfigFrom(
      __DIR__ . '/../config/pagecomposer.php',
      'page-composer'
    );
    //register commands
    $this->commands([
      Console\Commands\MakeElementCommand::class,
      Console\Commands\InstallCommand::class,
    ]);
  }
}
