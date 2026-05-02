<?php

namespace Flobbos\PageComposer\Tests;

use Flobbos\PageComposer\PageComposerServiceProvider;
use Flobbos\PageComposer\Tests\Fixtures\StubElement;
use Flobbos\PageComposer\Tests\Fixtures\User;
use Flobbos\TranslatableDB\TranslatableDBServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The page-composer-elements.* Livewire components are published
        // into the host app on install. In tests we register a single
        // stub under each element name we use so the orchestrator's view
        // can render rows that contain column items.
        foreach (['text', 'photo', 'youtube'] as $component) {
            Livewire::component('page-composer-elements.' . $component, StubElement::class);
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            LivewireServiceProvider::class,
            TranslatableDBServiceProvider::class,
            PageComposerServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('auth.providers.users.model', User::class);

        // TranslatableDB needs to know which model represents languages.
        $app['config']->set('translatable-db.language_model', \Flobbos\PageComposer\Models\Language::class);

        // BugComponent references this; set a default so nothing blows up.
        $app['config']->set('pagecomposer.bug_user', 1);
        $app['config']->set('pagecomposer.bug_notifications', false);

        // Route middleware — strip auth so tests can hit routes directly.
        $app['config']->set('pagecomposer.middleware', ['web']);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
    }
}
