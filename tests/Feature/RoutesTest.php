<?php

use Flobbos\PageComposer\Models\Category;
use Flobbos\PageComposer\Models\Page;
use Flobbos\PageComposer\Models\PageTranslation;
use Flobbos\PageComposer\Tests\Fixtures\User;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

beforeEach(function () {
    View::addLocation(__DIR__ . '/../Fixtures/views');
    // Livewire's default full-page layout is layouts::app.
    View::addNamespace('layouts', __DIR__ . '/../Fixtures/views/layouts');
});

/**
 * Package routes are registered at boot from the middleware config. The
 * base TestCase swaps that config for ['web'], so reload them with the
 * middleware under test.
 */
function reloadPackageRoutes(array|string $middleware): void
{
    config(['pagecomposer.middleware' => $middleware]);

    app('router')->setRoutes(new RouteCollection());
    Route::get('login', fn() => 'login')->name('login');
    require __DIR__ . '/../../src/routes/web.php';
    app('router')->getRoutes()->refreshNameLookups();
}

function actingUser(): User
{
    // No users table in the testbench skeleton; the auth layer only needs an id.
    return (new User())->forceFill(['id' => 1, 'name' => 'Editor']);
}

it('ships web plus auth as the default middleware', function () {
    $config = require __DIR__ . '/../../src/config/pagecomposer.php';

    expect($config['middleware'])->toBe(['web', 'auth']);
});

it('redirects guests to the login page with the default middleware', function () {
    reloadPackageRoutes(['web', 'auth']);

    $this->get('/page-composer/pages')->assertRedirect('/login');
});

it('lets the host app restrict access with a can: ability', function () {
    Gate::define('manage-pages', fn($user) => $user->name === 'Admin');
    reloadPackageRoutes(['web', 'auth', 'can:manage-pages']);

    $this->actingAs(actingUser())->get('/page-composer/pages')->assertForbidden();
});

it('lets an authorized user through', function () {
    Gate::define('manage-pages', fn() => true);
    reloadPackageRoutes(['web', 'auth', 'can:manage-pages']);

    $this->actingAs(actingUser())->get('/page-composer/pages')->assertOk();
});

it('404s the edit route for a non-numeric page id', function () {
    $this->get('/page-composer/pages/abc/edit')->assertNotFound();
});

it('404s the editor for a page that does not exist', function () {
    $this->get('/page-composer/pages/99999/edit')->assertNotFound();
});

it('404s the editor for a template that does not exist', function () {
    $this->get('/page-composer/pages/create?template=99999')->assertNotFound();
});

function publishedPage(bool $published = true): PageTranslation
{
    $language = seedLanguages(['en'])->first();
    $element = seedElement('Text', 'text');

    $page = new Page();
    $page->name = 'Hello';
    $page->category_id = Category::create([])->id;
    $page->is_published = $published;
    $page->published_on = now()->subDay();
    $page->save();

    $row = $page->rows()->create(['language_id' => $language->id]);
    $column = $row->columns()->create(['column_size' => 12]);
    $column->column_items()->create(['element_id' => $element->id, 'content' => ['text' => 'Rendered body']]);

    return $page->translations()->create([
        'language_id' => $language->id,
        'content' => ['title' => 'Hello'],
        'slug' => 'hello',
    ]);
}

it('renders a published page on the preview route with the package view', function () {
    config(['pagecomposer.frontend_layout' => 'layouts.frontend']);
    publishedPage();

    $this->get('/page-composer-preview/hello')
        ->assertOk()
        ->assertSee('data-frontend-layout', false)
        ->assertSee('Rendered body');
});

it('404s the preview route for an unpublished page', function () {
    publishedPage(published: false);

    $this->get('/page-composer-preview/hello')->assertNotFound();
});
