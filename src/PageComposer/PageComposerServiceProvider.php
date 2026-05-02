<?php

namespace Flobbos\PageComposer;


use Livewire\Livewire;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Flobbos\PageComposer\Livewire\BugComponent;
use Flobbos\PageComposer\Livewire\CategoryComponent;
use Flobbos\PageComposer\Livewire\ColumnComponent;
use Flobbos\PageComposer\Livewire\CommentComponent;
use Flobbos\PageComposer\Livewire\DatePicker;
use Flobbos\PageComposer\Livewire\ElementComponent;
use Flobbos\PageComposer\Livewire\ElementList;
use Flobbos\PageComposer\Livewire\ImageUploadComponent;
use Flobbos\PageComposer\Livewire\LanguageComponent;
use Flobbos\PageComposer\Livewire\MultiSelect;
use Flobbos\PageComposer\Livewire\MultiSelectInput;
use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Livewire\PageIndex;
use Flobbos\PageComposer\Livewire\RowComponent;
use Flobbos\PageComposer\Livewire\SelectInput;
use Flobbos\PageComposer\Livewire\TagComponent;
use Flobbos\PageComposer\Livewire\TemplateComponent;
use Flobbos\PageComposer\View\Components\BaseElement;

class PageComposerServiceProvider extends ServiceProvider
{
  /**
   * Livewire components shipped by this package. Each one is registered
   * under the kebab-cased class basename, matching Livewire 4's default
   * tag convention (BugComponent -> <livewire:bug-component />).
   */
  private const LIVEWIRE_COMPONENTS = [
    BugComponent::class,
    CategoryComponent::class,
    ColumnComponent::class,
    CommentComponent::class,
    DatePicker::class,
    ElementComponent::class,
    ElementList::class,
    ImageUploadComponent::class,
    LanguageComponent::class,
    MultiSelect::class,
    MultiSelectInput::class,
    PageComposer::class,
    PageIndex::class,
    RowComponent::class,
    SelectInput::class,
    TagComponent::class,
    TemplateComponent::class,
  ];

  public function boot(): void
  {
    foreach (self::LIVEWIRE_COMPONENTS as $componentClass) {
      Livewire::component(Str::kebab(class_basename($componentClass)), $componentClass);
    }

    //Blade components
    Blade::component('page-composer::base-element', BaseElement::class);

    //Publish config
    $this->publishes([
      __DIR__ . '/../config/pagecomposer.php' => config_path('pagecomposer.php'),
    ], 'page-composer-config');

    //Publish migrations
    $this->publishes([
      __DIR__ . '/../database/migrations/' => database_path('migrations'),
    ], 'page-composer-migrations');

    //Publishes base elements
    $this->publishes([
      __DIR__ . '/Livewire/Elements' => app_path('/Livewire/PageComposerElements'),
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
    ]);
  }
}
