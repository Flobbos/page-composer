<?php

namespace Flobbos\PageComposer\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class MakeElementCommand extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'page-composer:element';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate new pagebuilder-element';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Element';

  /**
   * Get the stub file for the generator.
   *
   * @return array[]
   */
  protected function getStub(): array
  {
    return [
      'class' => [
        'src' => resource_path('stubs/Element.php.stub'),
        'dest' => app_path('Http/Livewire/Elements')
      ],
      'view' => [
        'src' => resource_path('stubs/element.blade.php.stub'),
        'dest' => resource_path('views/livewire/elements')
      ]
    ];
  }

  public function handle()
  {
    // Generate class
    $this->generateClass();

    // Generate view
    $this->generateView();
  }

  /**
   * Generate element class from stub
   *
   * @throws FileNotFoundException
   */
  protected function generateClass(): void
  {

    $qualifiedName = $this->getQualifiedName();
    $viewName = $this->getViewName();

    $replacements = [
      'class' => $qualifiedName,
      'view' => $viewName
    ];

    $this->generateStub('class', $qualifiedName . '.php', $replacements);

    $this->info($this->type . '-Class created successfully.');
  }

  /**
   * Generate element view from stub
   *
   * @throws FileNotFoundException
   */
  protected function generateView()
  {
    $viewName = $this->getViewName();

    $this->generateStub('view', $viewName . '.blade.php');

    $this->info($this->type . '-View created successfully.');
  }

  /**
   *  Generate stub file based on arguments
   *
   * @param string $stubKey
   *
   * @param string $destName
   *
   * @param array $replacements
   *
   * @throws FileNotFoundException
   */
  protected function generateStub(string $stubKey, string $destName, array $replacements = []): void
  {
    $stub = Arr::get($this->getStub(), $stubKey);

    ['src' => $src, 'dest' => $dest] = $stub;

    $stubFile = $this->replaceStubVars($this->files->get($src), $replacements);

    $this->makeDirectory($dest);

    $this->files->put($dest . '/' . $destName, $stubFile);
  }

  /**
   * Replace variables in stub-files
   *
   * @param string $src
   *
   * @param array $replacements
   *
   * @return string|string[]
   */
  protected function replaceStubVars(string $src, array $replacements = [])
  {
    $replace = array_map(static function ($str) {
      return "{{ {$str} }}";
    }, array_keys($replacements));

    return str_replace($replace, array_values($replacements), $src);
  }

  /**
   * Get qualified class name
   *
   * @return string
   */
  protected function getQualifiedName(): string
  {
    return Str::studly($this->getNameInput());
  }

  /**
   * Get view name
   *
   * @return string
   */
  protected function getViewName(): string
  {
    return Str::kebab($this->getNameInput());
  }
}
