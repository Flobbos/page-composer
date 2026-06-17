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

    /**
     * Element `component` names registered against StubElement. The package's
     * real element classes are only available after publishing to the host
     * app, so any Livewire element name the tests emit must be added here.
     */
    protected array $elementStubs = [
        'text',
        'photo',
        'headline-text',
        'hero-banner',
        'grid-cards',
        'bullet-list-features',
        'testimonials-trust-badges',
        'accordion-faq',
        'call-to-action-section',
        'you-tube',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach ($this->elementStubs as $name) {
            Livewire::component('page-composer-elements.' . $name, StubElement::class);
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
