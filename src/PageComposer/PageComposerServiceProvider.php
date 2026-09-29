<?php

namespace Flobbos\PageComposer;


use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Flobbos\PageComposer\View\Components\BaseElement;

class PageComposerServiceProvider extends ServiceProvider
{
  public function boot(): void
  {
    // Package components live under their own namespace so they can't
    // collide with the app's (<livewire:page-composer::date-picker />).
    Livewire::addNamespace('page-composer', classNamespace: 'Flobbos\\PageComposer\\Livewire');

    //Blade components
    Blade::component('page-composer::base-element', BaseElement::class);

    //Publish config
    $this->publishes([
      __DIR__ . '/../config/pagecomposer.php' => config_path('pagecomposer.php'),
    ], 'page-composer-config');

    //Publishes base elements
    $this->publishes([
      __DIR__ . '/../resources/stubs/elements' => app_path('/Livewire/PageComposerElements'),
      __DIR__ . '/../resources/views/livewire/elements' => resource_path('/views/livewire/page-composer-elements'),
      __DIR__ . '/../resources/views/components/page-composer/elements' => resource_path('/views/components/page-composer-elements'),
    ], 'page-composer-elements');

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
      'pagecomposer'
    );

    //register commands
    $this->commands([
      Console\Commands\MakeElementCommand::class,
      Console\Commands\InstallCommand::class,
      Console\Commands\SyncRowAvailableSpaceCommand::class,
      Console\Commands\DoctorCommand::class,
    ]);
  }
}
