<?php

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->photo = app_path('Livewire/PageComposerElements/Photo.php');
    config(['pagecomposer.middleware' => ['web', 'auth']]);
});

afterEach(function () {
    File::delete($this->photo);
});

it('passes on a clean install', function () {
    $this->artisan('page-composer:doctor')->assertSuccessful();
});

it('flags a published Photo element that still has the unvalidated upload', function () {
    File::ensureDirectoryExists(dirname($this->photo));
    File::put($this->photo, '<?php $filename = $this->photo->getClientOriginalExtension();');

    $this->artisan('page-composer:doctor')
        ->expectsOutputToContain('saves uploads without validating them')
        ->expectsOutputToContain('client-supplied extension')
        ->assertFailed();
});

it('accepts the patched Photo element', function () {
    File::ensureDirectoryExists(dirname($this->photo));
    File::copy(__DIR__ . '/../../src/resources/stubs/elements/Photo.php', $this->photo);

    $this->artisan('page-composer:doctor')->assertSuccessful();
});

it('flags middleware without web and the old image rules', function () {
    config([
        'pagecomposer.middleware' => 'auth:sanctum',
        'pagecomposer.rules' => ['pageData.slider_image' => 'sometimes:image'] + config('pagecomposer.rules'),
    ]);

    $this->artisan('page-composer:doctor')
        ->expectsOutputToContain("doesn't include 'web'")
        ->expectsOutputToContain('validates nothing')
        ->assertFailed();
});
