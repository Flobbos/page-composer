<?php

use Flobbos\PageComposer\Livewire\BugComponent;
use Flobbos\PageComposer\Livewire\Frontend\PageDisplay;
use Flobbos\PageComposer\Livewire\PageComposer;
use Flobbos\PageComposer\Livewire\PageIndex;
use Illuminate\Support\Facades\Route;

//Protected routes
Route::group(['middleware' => config('pagecomposer.middleware'), 'prefix' => 'page-composer', 'as' => 'page-composer::'], function () {
    Route::get('/', BugComponent::class)->name('dashboard');
    Route::get('pages', PageIndex::class)->name('pages.index');
    Route::get('pages/create', PageComposer::class)->name('pages.create');
    Route::get('pages/{page}/edit', PageComposer::class)->whereNumber('page')->name('pages.edit');
});

//Public preview route
Route::get('page-composer-preview/{slug}', PageDisplay::class)->middleware('web')->name('pages.detail');
