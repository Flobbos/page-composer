<?php

namespace Flobbos\PageComposer;


use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
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
use Flobbos\PageComposer\Livewire\RowComponent;
use Flobbos\PageComposer\Livewire\SelectInput;
use Flobbos\PageComposer\Livewire\TagComponent;
use Flobbos\PageComposer\Livewire\TemplateComponent;
use Livewire\Livewire;

class PageComposerServiceProvider extends ServiceProvider
{

  public function boot()
  {
    //Register components
    Livewire::component('bug-component', BugComponent::class);
    Livewire::component('category-component', CategoryComponent::class);
    Livewire::component('column-component', ColumnComponent::class);
    Livewire::component('comment-component', CommentComponent::class);
    Livewire::component('element-component', ElementComponent::class);
    Livewire::component('element-list', ElementList::class);
    Livewire::component('date-picker', DatePicker::class);
    Livewire::component('image-upload-component', ImageUploadComponent::class);
    Livewire::component('language-component', LanguageComponent::class);
    Livewire::component('multi-select', MultiSelect::class);
    Livewire::component('multi-select-input', MultiSelectInput::class);
    Livewire::component('page-composer', PageComposer::class);
    Livewire::component('page-index', CategoryComponent::class);
    Livewire::component('row-component', RowComponent::class);
    Livewire::component('select-input', SelectInput::class);
    Livewire::component('tag-component', TagComponent::class);
    Livewire::component('template-component', TemplateComponent::class);

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
